<?php
declare (strict_types=1);

require_once 'UserRecords.php';

function util_randstring (int $nbytes) :string {
   return bin2hex (random_bytes ($nbytes));
}

function util_bool_to_string (bool $val) :string {
   return $val===false ? 'FALSE' : 'TRUE';
}

function util_rsp_error (int $code, string $message) :string {
   return
      '{' . "\n" .
      '   "error_code":     ' . $code . ',' . "\n" .
      '   "error_message":  "' . $message . "\"\n" .
      '}';
}

function util_rsp_success () :string {
   return util_rsp_error (0, 'Success');
}

function util_rsp_success_table (array $table, int $page_nr, int $npages) :string {
   $prefix =
      '{' . "\n" .
      '   "error_code":     0,' . "\n" .
      '   "error_message":  "Success",' . "\n" .
      '   "page_number":    ' . $page_nr . ",\n" .
      '   "page_count":     ' . $npages . ",\n";
   $postfix = '}';

   $sdata = '"table": [' . "\n";
   $rdelim = '';
   foreach ($table as $row) {
      $srow = '[ ';
      $fdelim = '';
      foreach ($row as $field) {
         $srow .= $fdelim . '"' . $field . '"';
         $fdelim = ', ';
      }
      $srow .= " ]";
      $sdata .= $rdelim . $srow;
      $rdelim = ",\n";
   }
   $sdata .= "\n]";

   return $prefix . $sdata . $postfix;
}

function util_userTypeString (int $n) :string {
   switch ($n) {
   case 0:  return 'Administrator';
   case 1:  return 'Operator';
   case 2:  return 'Standard';
   }
   return 'Unknown';
}


function util_agent_remove (string $agent_name) :bool {
   global $g_dbconn_rw;

   $result = $g_dbconn_rw->query ('DELETE FROM tbl_agent WHERE c_agent=$1', array ($agent_name));
   return DBConnection::querySucceeded ($result);
}

function util_agent_add (string $agent_name, string $agent_password) :int {
   global $g_dbconn_rw;

   $salt = util_randstring (32);
   $hash = UserRecords::pwhash ($agent_name, $salt, $agent_password);

   $id = $g_dbconn_rw->query
      ('INSERT INTO tbl_agent (c_agent, c_salt, c_hash) VALUES ($1, $2, $3) RETURNING id;',
         array ($agent_name, $salt, $hash));

   if (DBConnection::querySucceeded ($id) === true) {
      return (int)$id[1][0];
   } else {
      error_log ("Failure");
      return -1;
   }
}

function util_preprint ($obj) {
   echo '<pre>' . print_r ($obj, true) . '</pre>';
}

function util_agent_store_metric (array $vdict) :int {

   global $g_dbconn_rw;

   // TODO: Parse ifstats and fsdata
   // [FS_DATA] => /dev/sda2,524272,4,524268,1%,/boot/efi$/dev/sda5,1424825072,215491696,1136886400,16%,/$/dev/sdb2,1952506704,1226793928,725712776,63%,/mnt/sdb2
   $fsdata = array ();
   $fsdata_rows = explode ('$', $vdict['FS_DATA']);
   $fs_total = 0;
   $fs_used = 0;
   $fs_free = 0;
   foreach ($fsdata_rows as $fsdata_row) {
      $fields = explode (',', $fsdata_row);
      $row = array ();
      $row['FS_DEV']       = $fields[0];
      $row['FS_TOTAL']     = $fields[1];
      $row['FS_USED']      = $fields[2];
      $row['FS_AVAILABLE'] = $fields[3];
      $row['FS_UTIL']      = explode ('%', $fields[4])[0];
      $row['FS_MNTPT']     = $fields[5];
      array_push ($fsdata, $row);
      $fs_total += intval ($row['FS_TOTAL']);
      $fs_used  += intval ($row['FS_USED']);
      $fs_free  += intval ($row['FS_AVAILABLE']);
   }

   // [IFSTATS_COLS] => eno0 wlx386b1cd61f5c virbr0 Total
   // [IFSTATS_VALUES] => 0.00 0.00 69.29 2.17 0.00 0.00 69.29 2.17
   $ifstats = array ();
   $ifstats_cols = explode (' ', $vdict['IFSTATS_COLS']);
   $ifstats_values = explode (' ', $vdict['IFSTATS_VALUES']);
   $if_input = 0;
   $if_output = 0;
   $i = 0;
   foreach ($ifstats_cols as $iface) {
      $ifstat = array ();
      $ifstat['IF_NAME']   = $iface;
      $ifstat['IF_INPUT']  = $ifstats_values[$i++];
      $ifstat['IF_OUTPUT'] = $ifstats_values[$i++];
      if ((strcmp ($ifstat['IF_INPUT'], '0.00')!=0) || (strcmp ($ifstat['IF_OUTPUT'], '0.00')!=0))
         array_push ($ifstats, $ifstat);
      $if_input += floatval ($ifstat['IF_INPUT']);
      $if_output += floatval ($ifstat['IF_OUTPUT']);
   }
   $if_total = $if_input + $if_output;

   $agent = $g_dbconn_rw->query
      ('SELECT id, c_salt, c_hash FROM tbl_agent WHERE c_agent=$1', array ($vdict['MPM_USER']));

   if (count ($agent) <= 1) {
      error_log ("[" . $vdict['MPM_USER'] . "]: agent does not exist");
      return -1;
   }

   $agent_id = $agent[1][0];
   $salt     = $agent[1][1];
   $hash     = $agent[1][2];

   // $g_dbconn_rw->query ('START TRANSACTION;', array ());

   $ins_fsdata_query = 'INSERT INTO tbl_diskmetrics ('
                            . 'c_metrics, '
                            . 'c_fs, '
                            . 'c_mountpoint, '
                            . 'c_size_mb, '
                            . 'c_used_mb, '
                            . 'c_free_mb, '
                            . 'c_usage '
                     . ') VALUES ('
                            . '$1, '
                            . '$2, '
                            . '$3, '
                            . '$4, '
                            . '$5, '
                            . '$6, '
                            . '$7 '
                     . ');';

   $ins_ifstats_query = 'INSERT INTO tbl_ifmetrics ('
                            . 'c_metrics, '
                            . 'c_ifname, '
                            . 'c_input, '
                            . 'c_output '
                     . ') VALUES ('
                            . '$1, '
                            . '$2, '
                            . '$3, '
                            . '$4 '
                     . ');';

   $ins_metrics_query = 'INSERT INTO tbl_metrics ('
                            . 'c_agent, '                   // 1
                            . 'c_server_ts, '               // 2
                            . 'c_client_ts, '               // 3
                            . 'c_local_user, '              // 4
                            . 'c_kernel, '                  // 5
                            . 'c_hostname, '                // 6
                            . 'c_arch, '                    // 7
                            . 'c_mem_total, '               // 8
                            . 'c_mem_used, '                // 9
                            . 'c_mem_free, '                // 10
                            . 'c_swap_total, '              // 11
                            . 'c_swap_used, '               // 12
                            . 'c_swap_free, '               // 13
                            . 'c_cpu_count, '               // 14
                            . 'c_loadavg, '                 // 15
                            . 'c_open_sockets, '            // 16
                            . 'c_fd_allocated, '            // 17
                            . 'c_fd_unused, '               // 18
                            . 'c_fd_limit, '                // 19
                            . 'c_diskio_units, '            // 20
                            . 'c_diskio_tp_s, '             // 21
                            . 'c_diskio_read_s, '           // 22
                            . 'c_diskio_write_s, '          // 23
                            . 'c_diskio_discard_s, '        // 24
                            . 'c_diskio_read, '             // 25
                            . 'c_diskio_write, '            // 26
                            . 'c_diskio_discard, '          // 27
                            . 'c_fs_count, '                // 28
                            . 'c_if_count, '                // 29
                            . 'c_fs_total, '                // 30
                            . 'c_fs_used, '                 // 31
                            . 'c_fs_free, '                 // 32
                            . 'c_if_input, '                // 33
                            . 'c_if_output, '               // 34
                            . 'c_if_total'                  // 35
                  . ') VALUES ('
                            . '$1, '
                            . '$2, '
                            . '$3, '
                            . '$4, '
                            . '$5, '
                            . '$6, '
                            . '$7, '
                            . '$8, '
                            . '$9, '
                            . '$10, '
                            . '$11, '
                            . '$12, '
                            . '$13, '
                            . '$14, '
                            . '$15, '
                            . '$16, '
                            . '$17, '
                            . '$18, '
                            . '$19, '
                            . '$20, '
                            . '$21, '
                            . '$22, '
                            . '$23, '
                            . '$24, '
                            . '$25, '
                            . '$26, '
                            . '$27, '
                            . '$28, '
                            . '$29, '
                            . '$30, '
                            . '$31, '
                            . '$32, '
                            . '$33, '
                            . '$34, '
                            . '$35  '
                  . ') RETURNING id;';

   $ins_metrics_params = array ();

   array_push ($ins_metrics_params, $agent_id);                   // 1
   array_push ($ins_metrics_params, date ("c", time ()));         // 2
   array_push ($ins_metrics_params, $vdict['TSTAMP']);            // 3
   array_push ($ins_metrics_params, $vdict['LOCAL_USER']);        // 4
   array_push ($ins_metrics_params, $vdict['KERNEL']);            // 5
   array_push ($ins_metrics_params, $vdict['HOSTNAME']);          // 6
   array_push ($ins_metrics_params, $vdict['ARCH']);              // 7
   array_push ($ins_metrics_params, $vdict['MEMORY_TOTAL']);      // 8
   array_push ($ins_metrics_params, $vdict['MEMORY_USED']);       // 9
   array_push ($ins_metrics_params, $vdict['MEMORY_FREE']);       // 10
   array_push ($ins_metrics_params, $vdict['SWAP_TOTAL']);        // 11
   array_push ($ins_metrics_params, $vdict['SWAP_USED']);         // 12
   array_push ($ins_metrics_params, $vdict['SWAP_FREE']);         // 13
   array_push ($ins_metrics_params, $vdict['CPU_COUNT']);         // 14
   array_push ($ins_metrics_params, $vdict['LOADAVG']);           // 15
   array_push ($ins_metrics_params, $vdict['SOCKETS_OPEN']);      // 16
   array_push ($ins_metrics_params, $vdict['FD_ALLOCATED']);      // 17
   array_push ($ins_metrics_params, $vdict['FD_UNUSED']);         // 18
   array_push ($ins_metrics_params, $vdict['FD_LIMIT']);          // 19
   array_push ($ins_metrics_params, $vdict['DISKIO_UNITS']);      // 20
   array_push ($ins_metrics_params, $vdict['DISKIO_TPS']);        // 21
   array_push ($ins_metrics_params, $vdict['DISKIO_READS']);      // 22
   array_push ($ins_metrics_params, $vdict['DISKIO_WRITES']);     // 23
   array_push ($ins_metrics_params, $vdict['DISKIO_DISCARDS']);   // 24
   array_push ($ins_metrics_params, $vdict['DISKIO_READ']);       // 25
   array_push ($ins_metrics_params, $vdict['DISKIO_WRITE']);      // 26
   array_push ($ins_metrics_params, $vdict['DISKIO_DISCARD']);    // 27
   array_push ($ins_metrics_params, $vdict['FS_COUNT']);          // 28
   array_push ($ins_metrics_params, count ($ifstats));            // 29
   array_push ($ins_metrics_params, $fs_total);                   // 30
   array_push ($ins_metrics_params, $fs_used);                    // 31
   array_push ($ins_metrics_params, $fs_free);                    // 32
   array_push ($ins_metrics_params, $if_input);                   // 33
   array_push ($ins_metrics_params, $if_output);                  // 34
   array_push ($ins_metrics_params, $if_total);                   // 35

   $result = $g_dbconn_rw->query ($ins_metrics_query, $ins_metrics_params);
   if (DBConnection::querySucceeded ($result) === false) {
      error_log ("Query failure, aborting");
      $g_dbconn_rw->query ('ROLLBACK;', array ());
      return -1;
   }
   $metric_id = $result[1][0];

   foreach ($fsdata as $fs) {
      $ins_fsdata_params = array (
         $metric_id,
         $fs['FS_DEV'],
         $fs['FS_MNTPT'],
         $fs['FS_TOTAL'],
         $fs['FS_USED'],
         $fs['FS_AVAILABLE'],
         $fs['FS_UTIL'],
      );
      $result = $g_dbconn_rw->query ($ins_fsdata_query, $ins_fsdata_params);
      if (DBConnection::querySucceeded ($result) === false) {
         error_log ("Query failure on diskmetrics insertion, aborting");
         return -1;
      }
   }

   foreach ($ifstats as $iface) {
      $ins_ifstats_params = array (
         $metric_id,
         $iface['IF_NAME'],
         $iface['IF_INPUT'],
         $iface['IF_OUTPUT'],
      );
      $result = $g_dbconn_rw->query ($ins_ifstats_query, $ins_ifstats_params);
      if (DBConnection::querySucceeded ($result) === false) {
         error_log ("Query failure on ifmetrics insertion, aborting");
         return -1;
      }
   }

   // $g_dbconn_rw->query ('COMMIT;', array ());

   return intval ($metric_id);
}

?>

<?php
declare (strict_types=1);

require_once 'UserRecords.php';

function util_randstring (int $nbytes) :string {
   return bin2hex (random_bytes ($nbytes));
}

function bool_to_string (bool $val) :string {
   return $val===false ? 'FALSE' : 'TRUE';
}

function util_agent_remove (string $agent_name) :bool {
   global $g_dbconn_rw;

   $result = $g_dbconn_rw->query ('DELETE FROM tbl_user WHERE c_user=$1', array ($agent_name));
   return DBConnection::querySucceeded ($result);
}

function util_agent_add (string $agent_name, string $agent_password) :int {
   global $g_dbconn_rw;

   $salt = util_randstring (32);
   $hash = UserRecords::pwhash ($agent_name, $salt, $agent_password);

   $id = $g_dbconn_rw->query
      ('INSERT INTO tbl_user (c_user, c_salt, c_hash) VALUES ($1, $2, $3) RETURNING id;',
         array ($agent_name, $salt, $hash));

   if (DBConnection::querySucceeded ($id) === true) {
      return (int)$id[1][0];
   } else {
      return -1;
   }
}

function util_agent_store_metric (array $vdict) :int {

   // "MPM_USER"
   // "MPM_PASSWORD"
   // "IFSTATS_COLS"
   // "IFSTATS_VALUES"
   // "DISKIO"
   // "FS_DATA"
   // "END"

   $user_record = $this->dbhandle->query
      ('SELECT id, c_salt, c_hash FROM tbl_user WHERE c_user=$1', array ($vdict['MPM_USER']));
   $id   = $user_record[1][0];
   $salt = $user_record[1][1];
   $hash = $user_record[1][2];

   pg_send_execute ($this->rwdb, 'START TRANSACTION;', array ());
   // TODO: This is incomplete
   //
   pg_prepare ('ins_metrics', 'INSERT INTO tbl_metrics ('
                            . 'c_user, '
                            . 'c_server_ts, '
                            . 'c_client_ts, '
                            . 'c_local_user, '
                            . 'c_kernel, '
                            . 'c_hostname, '
                            . 'c_arch, '
                            . 'c_mem_total, '
                            . 'c_mem_used, '
                            . 'c_mem_free, '
                            . 'c_swap_total, '
                            . 'c_swap_used, '
                            . 'c_swap_free, '
                            . 'c_cpu_count, '
                            . 'c_loadavg, '
                            . 'c_open_sockets, '
                            . 'c_diskio_units, '
                            . 'c_diskio_tp_s, '
                            . 'c_diskio_read_s, '
                            . 'c_diskio_write_s, '
                            . 'c_diskio_discard_s, '
                            . 'c_diskio_tp, '
                            . 'c_diskio_read, '
                            . 'c_diskio_write, '
                            . 'c_diskio_discard, '
                            . 'c_fs_count, '
                            . 'c_if_count'
                            . ') values ('
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
                            . '$27'
                            . ')');
   $ins_params = array ();
   array_push ($ins_params, $this->user_record);
   array_push (time ());
   array_push ($vdict['TSTAMP']);
   array_push ($vdict['LOCAL_USER']);
   array_push ($vdict['KERNEL']);
   array_push ($vdict['HOSTNAME']);
   array_push ($vdict['ARCH']);
   array_push ($vdict['MEMORY_TOTAL']);
   array_push ($vdict['MEMORY_USED']);
   array_push ($vdict['MEMORY_FREE']);
   array_push ($vdict['SWAP_TOTAL']);
   array_push ($vdict['SWAP_USED']);
   array_push ($vdict['SWAP_FREE']);
   array_push ($vdict['CPU_COUNT']);
   array_push ($vdict['LOADAVG']);
   array_push ($vdict['SOCKETS_OPEN']);
   array_push ($vdict['DISKIO_UNITS']);
   array_push ($vdict['DISKIO_TPS']);
   array_push ($vdict['DISKIO_READS']);
   array_push ($vdict['DISKIO_WRITES']);
   array_push ($vdict['DISKIO_DISCARDS']);
   array_push ($vdict['DISKIO_TP']);
   array_push ($vdict['DISKIO_READ']);
   array_push ($vdict['DISKIO_WRITE']);
   array_push ($vdict['DISKIO_DISCARD']);
   array_push ($vdict['FS_COUNT']);

   pg_send_execute ($this->rwdb, 'COMMIT;', array ());
}
?>

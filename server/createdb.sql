
-- Purposefully not using EXISTS clause. Errors must be reported if tables
-- already exist so that user does not use an existing db by mistake.


CREATE TABLE tbl_user (
   id          BIGSERIAL PRIMARY KEY,
   c_user      VARCHAR(250) UNIQUE NOT NULL,
   c_salt      VARCHAR(250) NOT NULL,
   c_hash      VARCHAR(250) NOT NULL
);


CREATE TABLE tbl_metrics (
   id                      BIGSERIAL PRIMARY KEY,
   c_user                  BIGINT,
   c_server_ts             TIMESTAMP WITH TIMEZONE NOT NULL,
   c_client_ts             TIMESTAMP WITH TIMEZONE NOT NULL,
   c_local_user            VARCHAR(250) NOT NULL,
   c_kernel                VARCHAR(250) NOT NULL,
   c_hostname              VARCHAR(250) NOT NULL,
   c_arch                  VARCHAR(50) NOT NULL,
   c_mem_total             BIGINT NOT NULL,
   c_mem_used              BIGINT NOT NULL,
   c_mem_free              BIGINT NOT NULL,
   c_swap_total            BIGINT NOT NULL,
   c_swap_used             BIGINT NOT NULL,
   c_swap_free             BIGINT NOT NULL,
   c_cpu_count             SMALLINT NOT NULL,
   c_loadavg               REAL NOT NULL,
   c_open_sockets          INTEGER NOT NULL,
   c_diskio_units          VARCHAR(10) NOT NULL,
   c_diskio_tp_s           REAL NOT NULL,
   c_diskio_read_s         REAL NOT NULL,
   c_diskio_write_s        REAL NOT NULL,
   c_diskio_discard_s      REAL NOT NULL,
   c_diskio_tp             REAL NOT NULL,
   c_diskio_read           REAL NOT NULL,
   c_diskio_write          REAL NOT NULL,
   c_diskio_discard        REAL NOT NULL,
   c_fs_count              SMALLINT NOT NULL,
   c_if_count              SMALLINT NOT NULL,
   FOREIGN KEY (c_user) references (tbl_user)
);


CREATE TABLE tbl_max_thresholds (
   id                      BIGSERIAL PRIMARY KEY,
   c_hostname              VARCHAR(250) NOT NULL,
   c_mem_used              BIGINT NULL,
   c_swap_used             BIGINT NULL,
   c_loadavg               REAL NULL,
   c_open_sockets          INTEGER NULL,
   c_diskio_tp_s           REAL NULL,
   c_diskio_read_s         REAL NULL,
   c_diskio_write_s        REAL NULL,
   c_diskio_discard_s      REAL NULL,
   c_diskio_tp             REAL NULL,
   c_diskio_read           REAL NULL,
   c_diskio_write          REAL NULL,
   c_diskio_discard        REAL NULL,
   c_if_input              REAL NULL,
   c_if_output             REAL NULL,
   c_disk_used_mb          BIGINT NULL,
   c_disk_usage            REAL NULL
);


CREATE TABLE tbl_ifmetrics (
   id             BIGSERIAL PRIMARY KEY,
   c_metrics      BIGINT,
   c_ifname       VARCHAR(250) NOT NULL,
   c_input        REAL NOT NULL,
   c_output       REAL NOT NULL,
   FOREIGN KEY (c_metrics) references (tbl_metrics)
);


CREATE TABLE tbl_diskmetrics (
   id                BIGSERIAL PRIMARY KEY,
   c_metrics         BIGINT,
   c_fs              VARCHAR(250) NOT NULL,
   c_mountpoint      VARCHAR(250) NOT NULL,
   c_size_mb         BIGINT NOT NULL,
   c_used_mb         BIGINT NOT NULL,
   c_free_mb         BIGINT NOT NULL,
   c_usage           REAL NOT NULL,
   FOREIGN KEY (c_metrics) references (tbl_metrics)
);


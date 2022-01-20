
-- Purposefully not using EXISTS clause. Errors must be reported if tables
-- already exist so that user does not use an existing db by mistake.

-- To create an index: CREATE INDEX idx_TABLE_COL ON TABLE(COL);
-- To create an index: CREATE INDEX idx_TABLE_COL ON TABLE(COL);

CREATE TABLE tbl_agent (
   id          BIGSERIAL PRIMARY KEY,
   c_agent     VARCHAR(250) UNIQUE NOT NULL,
   c_salt      VARCHAR(250) NOT NULL,
   c_hash      VARCHAR(250) NOT NULL
);


CREATE TABLE tbl_metrics (
   id                      BIGSERIAL PRIMARY KEY,
   c_agent                 BIGINT,
   c_server_ts             TIMESTAMP WITH TIME ZONE NOT NULL,
   c_client_ts             TIMESTAMP WITH TIME ZONE NOT NULL,
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
   c_open_sockets          BIGINT NOT NULL,
   c_fd_allocated          BIGINT NOT NULL,
   c_fd_unused             BIGINT NOT NULL,
   c_fd_limit              BIGINT NOT NULL,
   c_diskio_units          VARCHAR(10) NOT NULL,
   c_diskio_tp_s           REAL NOT NULL,
   c_diskio_read_s         REAL NOT NULL,
   c_diskio_write_s        REAL NOT NULL,
   c_diskio_discard_s      REAL NOT NULL,
   c_diskio_read           REAL NOT NULL,
   c_diskio_write          REAL NOT NULL,
   c_diskio_discard        REAL NOT NULL,
   c_fs_count              SMALLINT NOT NULL,
   c_if_count              SMALLINT NOT NULL,
   c_fs_total              BIGINT NOT NULL,
   c_fs_used               BIGINT NOT NULL,
   c_fs_free               BIGINT NOT NULL,
   c_if_input              REAL NOT NULL,
   c_if_output             REAL NOT NULL,
   c_if_total              REAL NOT NULL,
   FOREIGN KEY (c_agent) references tbl_agent(id)
);

CREATE INDEX idx_tbl_metrics_server_ts ON tbl_metrics (c_server_ts);
CREATE INDEX idx_tbl_metrics_client_ts ON tbl_metrics (c_client_ts);
CREATE INDEX idx_tbl_metrics_hostname ON tbl_metrics (c_hostname);
CREATE INDEX idx_tbl_metrics_mem_free ON tbl_metrics (c_mem_free);
CREATE INDEX idx_tbl_metrics_swap_free ON tbl_metrics (c_swap_free);
CREATE INDEX idx_tbl_metrics_cpu_count ON tbl_metrics (c_cpu_count);
CREATE INDEX idx_tbl_metrics_loadavg ON tbl_metrics (c_loadavg);
CREATE INDEX idx_tbl_metrics_open_sockets ON tbl_metrics (c_open_sockets);
CREATE INDEX idx_tbl_metrics_diskio_tp_s ON tbl_metrics (c_diskio_tp_s);
CREATE INDEX idx_tbl_metrics_diskio_read_s ON tbl_metrics (c_diskio_read_s);
CREATE INDEX idx_tbl_metrics_diskio_write_s ON tbl_metrics (c_diskio_write_s);
CREATE INDEX idx_tbl_metrics_fs_used ON tbl_metrics (c_fs_used);
CREATE INDEX idx_tbl_metrics_fs_free ON tbl_metrics (c_fs_free);
CREATE INDEX idx_tbl_metrics_if_input ON tbl_metrics (c_if_input);
CREATE INDEX idx_tbl_metrics_if_output ON tbl_metrics (c_if_output);
CREATE INDEX idx_tbl_metrics_if_total ON tbl_metrics (c_if_total);

CREATE TABLE tbl_max_thresholds (
   id                      BIGSERIAL PRIMARY KEY,
   c_hostname              VARCHAR(250) NOT NULL,
   c_mem_used              BIGINT NULL,
   c_mem_free              BIGINT NULL,
   c_swap_used             BIGINT NULL,
   c_swap_free             BIGINT NULL,
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

CREATE INDEX idx_tbl_max_thresholds_hostname ON tbl_max_thresholds (c_hostname);

CREATE TABLE tbl_ifmetrics (
   id             BIGSERIAL PRIMARY KEY,
   c_metrics      BIGINT,
   c_ifname       VARCHAR(250) NOT NULL,
   c_input        REAL NOT NULL,
   c_output       REAL NOT NULL,
   FOREIGN KEY (c_metrics) references tbl_metrics (id)
);

CREATE INDEX idx_tbl_ifmetrics_metrics ON tbl_ifmetrics (c_metrics);


CREATE TABLE tbl_diskmetrics (
   id                BIGSERIAL PRIMARY KEY,
   c_metrics         BIGINT,
   c_fs              VARCHAR(250) NOT NULL,
   c_mountpoint      VARCHAR(250) NOT NULL,
   c_size_mb         BIGINT NOT NULL,
   c_used_mb         BIGINT NOT NULL,
   c_free_mb         BIGINT NOT NULL,
   c_usage           SMALLINT NOT NULL,
   FOREIGN KEY (c_metrics) references tbl_metrics (id)
);

CREATE INDEX idx_tbl_diskmetrics_metrics ON tbl_diskmetrics (c_metrics);


#!/bin/bash

# Run this script from cron, once a minute. Note that the one and only c/line
# argument is a URL for the web service that receives the output of this
# script.

if [ -z "$1" ]; then
   echo Need to specify url to use when uploading info
   exit 127
fi

MPM_USER=""
. $HOME/.mpm_agent.creds

if [ -z "$MPM_USER" ]; then
   echo '$MPM_USER not set - ensure that it is set in ' $HOME/.mpm_agent.creds
   exit 1
fi

if [ -z "$MPM_PASSWORD" ]; then
   echo '$MPM_PASSWORD not set - ensure that it is set in ' $HOME/.mpm_agent.creds
   exit 2
fi

TSTAMP="`date -Iseconds`"
KERNEL="`uname -a`"
HOSTNAME="`hostname -f`"

TMPVAR="`free`"
STATS_MEMORY="`echo $TMPVAR | cut -f8-17 -d \  `"
MEMORY_TOTAL="`echo $STATS_MEMORY | cut -f 1 -d \  `"
MEMORY_USED="`echo $STATS_MEMORY | cut -f 2 -d \  `"
MEMORY_FREE="`echo $STATS_MEMORY | cut -f 3 -d \  `"
SWAP_TOTAL="`echo $STATS_MEMORY | cut -f 8 -d \  `"
SWAP_USED="`echo $STATS_MEMORY | cut -f 9 -d \  `"
SWAP_FREE="`echo $STATS_MEMORY | cut -f 10 -d \  `"
LOADAVG="`cut -f 1 -d \  /proc/loadavg`"
SOCKETS_OPEN="`ss -s | grep -m1 Total | cut -f 2 -d \  `"
DISKIO="`iostat -g all -m | grep all | sed 's/^ \+//g'`"
DISKIO_UNITS="MB/s"
DISKIO_TPS="`echo $DISKIO | cut -f 2 -d \  `"
DISKIO_READS="`echo $DISKIO | cut -f 3 -d \  `"
DISKIO_WRITES="`echo $DISKIO | cut -f 4 -d \  `"
DISKIO_DISCARDS="`echo $DISKIO | cut -f 5 -d \  `"
DISKIO_READ="`echo $DISKIO | cut -f 6 -d \  `"
DISKIO_WRITE="`echo $DISKIO | cut -f 7 -d \  `"
DISKIO_DISCARD="`echo $DISKIO | cut -f 8 -d \  `"

ifstat -T 1 1 | sed 's/^ \+//g; s/ \+$//g' > /tmp/ifstats
IFSTATS_COLS="`head -n1 /tmp/ifstats`"
IFSTATS_VALUES="`tail -n1 /tmp/ifstats`"

df -B 1M | grep -E [1-9]% | grep -v tmpfs | sed 's/ \+/,/g' | sort > /tmp/df
FS_COUNT="`wc -l /tmp/df | cut -f 1 -d \  `"
TMP="`cat /tmp/df`"
FS_DATA="`echo $TMP | sed 's/ /$/g'`"

lscpu > /tmp/cpu
CPU_COUNT="`grep '^CPU(s):' /tmp/cpu | cut -f 2 -d : | sed 's/ \+//g'`"
ARCH="`grep '^Architecture' /tmp/cpu | cut -f 2 -d : | sed 's/ \+//g'`"

echo '{'                                                 > /tmp/mpm_agent.body
echo '   '\"MPM_USER\"':            ' \"$MPM_USER\",          >> /tmp/mpm_agent.body
echo '   '\"MPM_PASSWORD\"':        ' \"$MPM_PASSWORD\",      >> /tmp/mpm_agent.body
echo '   '\"LOCAL_USER\"':          ' \""`whoami`"\",         >> /tmp/mpm_agent.body
echo '   '\"KERNEL\"':              ' \"$KERNEL\",            >> /tmp/mpm_agent.body
echo '   '\"TSTAMP\"':              ' \"$TSTAMP\",            >> /tmp/mpm_agent.body
echo '   '\"HOSTNAME\"':            ' \"$HOSTNAME\",          >> /tmp/mpm_agent.body
echo '   '\"MEMORY_TOTAL\"':        ' \"$MEMORY_TOTAL\",      >> /tmp/mpm_agent.body
echo '   '\"MEMORY_USED\"':         ' \"$MEMORY_USED\",       >> /tmp/mpm_agent.body
echo '   '\"MEMORY_FREE\"':         ' \"$MEMORY_TOTAL\",      >> /tmp/mpm_agent.body
echo '   '\"SWAP_TOTAL\"':          ' \"$SWAP_TOTAL\",        >> /tmp/mpm_agent.body
echo '   '\"SWAP_USED\"':           ' \"$SWAP_USED\",         >> /tmp/mpm_agent.body
echo '   '\"SWAP_FREE\"':           ' \"$SWAP_TOTAL\",        >> /tmp/mpm_agent.body
echo '   '\"LOADAVG\"':             ' \"$LOADAVG\",           >> /tmp/mpm_agent.body
echo '   '\"ARCH\"':                ' \"$ARCH\",              >> /tmp/mpm_agent.body
echo '   '\"CPU_COUNT\"':           ' \"$CPU_COUNT\",         >> /tmp/mpm_agent.body
echo '   '\"SOCKETS_OPEN\"':        ' \"$SOCKETS_OPEN\",      >> /tmp/mpm_agent.body
echo '   '\"IFSTATS_COLS\"':        ' \"$IFSTATS_COLS\",      >> /tmp/mpm_agent.body
echo '   '\"IFSTATS_VALUES\"':      ' \"$IFSTATS_VALUES\",    >> /tmp/mpm_agent.body
echo '   '\"DISKIO_UNITS\"':        ' \"$DISKIO_UNITS\",      >> /tmp/mpm_agent.body
echo '   '\"DISKIO\"':              ' \"$DISKIO\",            >> /tmp/mpm_agent.body
echo '   '\"DISKIO_TPS\"':          ' \"$DISKIO_TPS\",        >> /tmp/mpm_agent.body
echo '   '\"DISKIO_READS\"':        ' \"$DISKIO_READS\",      >> /tmp/mpm_agent.body
echo '   '\"DISKIO_WRITES\"':       ' \"$DISKIO_WRITES\",     >> /tmp/mpm_agent.body
echo '   '\"DISKIO_DISCARDS\"':     ' \"$DISKIO_DISCARDS\",   >> /tmp/mpm_agent.body
echo '   '\"DISKIO_READ\"':         ' \"$DISKIO_READ\",       >> /tmp/mpm_agent.body
echo '   '\"DISKIO_WRITE\"':        ' \"$DISKIO_WRITE\",      >> /tmp/mpm_agent.body
echo '   '\"DISKIO_DISCARD\"':      ' \"$DISKIO_DISCARD\",    >> /tmp/mpm_agent.body
echo '   '\"FS_COUNT\"':            ' \"$FS_COUNT\",          >> /tmp/mpm_agent.body
echo '   '\"FS_DATA\"':             ' \""$FS_DATA"\",         >> /tmp/mpm_agent.body
echo '   '\"END\"':                 ' \""ignore"\"            >> /tmp/mpm_agent.body
echo '}'                                                 >> /tmp/mpm_agent.body

curl -s\
     -X POST\
     -H 'Content-type: application/json'\
     --data '@/tmp/mpm_agent.body'\
     "$1"\
     > /tmp/mpm_agent.response

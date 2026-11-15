#!/bin/bash
# Dumps the database every night at 04:00 and keeps the 7 newest dumps
set -euo pipefail

while true; do
    next=$(date -d 04:00 +%s)
    (( next > $(date +%s) )) || next=$(date -d 'tomorrow 04:00' +%s)
    sleep $(( next - $(date +%s) ))
    file=/backups/tw2-stats-$(date +%F).dump
    # Written under another name first, so a failed dump never counts as one
    if pg_dump -Fc -d tw2-stats -f /backups/.partial; then
        mv /backups/.partial "$file"
        ls -1t /backups/*.dump | tail -n +8 | xargs -r rm --
    else
        rm -f /backups/.partial
    fi
done

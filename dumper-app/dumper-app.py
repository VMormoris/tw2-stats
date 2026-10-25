from time import sleep
from WorldDumper import WorldDumper
import json
import os
import psycopg2 as pgdb
import time

MIN = 60.0
HOUR = 60.0 * MIN
DAY = 24.0 * MIN

#Game accounts by server (the worlds' server column), see docker/dumper/accounts.example.json
ACCOUNTS = '/run/secrets/accounts'

if __name__ == '__main__':

    with open(ACCOUNTS, 'r') as file:
        accounts = json.load(file)

    while True:
        #Connects with the PG* environment variables
        try:
            conn = pgdb.connect('')
            cur = conn.cursor()
            cur.execute('SELECT wid, server, url FROM worlds WHERE running ORDER BY wid;')
            worlds = cur.fetchall()
            cur.close()
            conn.close()
        except pgdb.Error as error:
            print('Skipping this hour, the worlds could not be read:', error)
            worlds = []

        for (wid, server, url) in worlds:
            if server not in accounts:
                print('No account for server', server, 'of', wid)
                continue
            tokenfilepath = '.cache/' + wid + '.token'
            token = ''
            if os.path.isfile(tokenfilepath):
                with open(tokenfilepath, 'r') as file:
                    token = file.read()
            dumper = WorldDumper({
                'server': 'wss://' + url,
                'token': token,
                'name': accounts[server]['username'],
                'pass': accounts[server]['password'],
                'world': wid
            })
            dumper.start()
        now = time.mktime(time.strptime(time.strftime('%Y-%m-%d %H:00:00', time.localtime()), '%Y-%m-%d %H:00:00'))
        next = now + HOUR + 10 * MIN
        stime = next - time.mktime(time.localtime())
        sleep(stime)

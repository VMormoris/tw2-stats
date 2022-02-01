from time import sleep
from WorldDumper import WorldDumper
import json
import os
import time

MIN = 60.0
HOUR = 60.0 * MIN
DAY = 24.0 * MIN

if __name__ == '__main__':
    
    with open('config.json', 'r') as file:
        config = json.load(file)
    
    worlds = []
    for world in config:
        tokenfilepath = '.cache/' + world['world'] + '.token'
        if os.path.isfile(tokenfilepath):
            with open(tokenfilepath, 'r') as file:
                world['token'] = file.read()
        else:
            world['token'] = ''
        worlds.append(world)

    while True:
        for world in worlds:
            dumper = WorldDumper({
                'server': 'wss://' + world['server'],
                'token': world['token'],
                'name': world['username'],
                'pass': world['password'],
                'world': world['world']
            })
            dumper.start()
        now = time.mktime(time.strptime(time.strftime('%Y-%m-%d %H:00:00', time.gmtime()), '%Y-%m-%d %H:00:00'))
        next = now + HOUR + 10 * MIN
        stime = next - time.mktime(time.gmtime())
        sleep(stime)
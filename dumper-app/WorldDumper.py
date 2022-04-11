from tw2client import EXIT_FAILURE, tw2Client as tw2c
import psycopg2 as pgdb
import time

#Password for tw2-stats Database user
PASSWORD = '<your_password>'

upsert_tribes = '''
INSERT INTO tribes(id, name, nname, tag, active, members, points, offbash, defbash, totalbash, rankno, villages, vp, timestamp)
VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
ON CONFLICT (id) DO UPDATE SET
    id = %s,
    name = %s, nname = %s, tag = %s,
    active = %s,
    members = %s,
    points = %s,
    offbash = %s, defbash = %s, totalbash = %s,
    rankno = %s,
    villages = %s,
    vp = %s,
    timestamp = %s;
'''

upsert_players = '''
INSERT INTO players(id, name, nname, tid, villages, points, offbash, defbash, totalbash, rankno, vp, timestamp)
VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
ON CONFLICT (id) DO UPDATE SET
    id = %s,
    name = %s, nname = %s,
    tid = %s,
    villages = %s,
    points = %s,
    offbash = %s, defbash = %s, totalbash = %s,
    rankno = %s,
    vp = %s,
    timestamp = %s;
'''

upsert_villages = '''
INSERT INTO villages (id, name, nname, pid, tid, x, y, points, provname, timestamp)
VALUES(%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
ON CONFLICT (id) DO UPDATE SET
    id = %s,
    name = %s, nname = %s,
    pid = %s, tid = %s,
    x = %s, y = %s,
    points = %s,
    provname = %s,
    timestamp = %s;
'''

update_world = '''
UPDATE worlds
SET
    "tribes" = %s,
    "players" = %s,
    "villages" = %s
WHERE "wid" = %s;
'''

class WorldDumper:
    '''Class that help on taking a world dump from tw2
    '''

    def __init__(self, settings):
        '''Constructs a new object
        '''
        self.__player_id = 0
        self.__data = { 'villages': [], 'players': [], 'tribes': [] }
        self.__left_areas = (500*500)/(50*50)
        self.__startX = 500 - 2 * 125
        self.__startY = 500 - 2 * 125
        self.__endX = 500 + 2 * 125
        self.__client = tw2c(settings['server'], settings['token'])
        self.__name = settings['name']
        self.__pass = settings['pass']
        self.__world = settings['world']
        self.__timestamp = None
        self.__state = False
        self.__command = { 
            'type': 'Map/getVillagesByArea',
            'data': {
                'x': self.__startX,
                'y': self.__startY
            }
        }
    
    def isFinished(self):
        return self.__state

    def start(self):
        '''Start the proccess of taking a dump
        '''
        ts = time.localtime()
        self.__state = False
        self.__timestamp = time.strftime('%Y-%m-%d %H:00:00', ts)
        print('Attempting loggin')
        self.__client.emit('Authentication/login', { 'name': self.__name, 'pass': self.__pass }, self.__onlogin)
        time.sleep(10)
        if self.__client.totalEmits() <= 2:
            print('Queue empty!!!')
            self.__state = False
            self.__client.restart()
            self.start()

    def __onlogin(self, obj):
        '''Callback for autetication attempt
        '''
        if obj['type'] == 'Login/success':
            self.__player_id = obj['data']['player_id']
            self.__client.emit('Authentication/selectCharacter', { 'id': self.__player_id, 'world_id': self.__world }, self.__onworld_selection)
        elif obj['type'] == 'System/error' or obj['type'] == 'Exception/ErrorException':
            print('Login failed')
            self.__client.disconnect()
        else:
            print('Ignoring:', obj)

    def __onworld_selection(self, obj):
        '''Callback for world selection
        '''
        if(obj['type'] != 'Authentication/characterSelected'):
            print('Ignoring:', obj)
            return
        token = obj['data']['tokenEmit']
        with open('.cache/'+ self.__world +'.token', 'w') as file:
            file.write(token)
        self.__client.setEmitToken(token)
        time.sleep(1.0)
        if self.__command['type'] == 'Map/getVillagesByArea':
            (x, y) = self.__command['data']['x'], self.__command['data']['y']
            self.__client.emit('Map/getVillagesByArea', { 'x': x, 'y': y, 'width': 50, 'height': 50, 'character_id': self.__player_id }, self.__onvillages)
        elif self.__command['type'] == 'Ranking/getTribeRanking':
            offset = self.__command['offset']
            self.__client.emit('Ranking/getTribeRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': offset, 'order_by': 'rank', 'order_dir': 0, 'query': '' }, self.__ontribes)
        elif self.__command['type'] == 'getCharacterRanking':
            offset = self.__command['offset']
            self.__client.emit('Ranking/getCharacterRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': offset, 'order_by': 'rank', 'order_dir': 0, 'query': ''}, self.__onplayers)


    def __onvillages(self, obj):
        '''Callback for villages
        '''
        if obj['type'] == 'System/error' or obj['type'] == 'Exception/ErrorException':
            time.sleep(120.0)
            self.__state = False
            self.__client.restart()
            self.start()
            return
        elif obj['type'] == 'Achievement/progress':
            print('Ignoring:', obj['type'])
            return

        if obj['type'] != 'Map/villageData':
            print(obj)
            if self.__command['type'] == 'Map/getVillagesByArea':
                self.__client.emit(self.__command['type'], {'x': self.__command['data']['x'], 'y': self.__command['data']['y'], 'width': 50, 'height': 50, 'character_id': self.__player_id }, self.__onvillages)
            else:
                self.__client.emit('Ranking/getTribeRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': 0, 'order_by': 'rank', 'order_dir': 0, 'query': '' }, self.__ontribes)
            return

        villages = obj['data']['villages']
        for village in villages:
            self.__data['villages'].append(village)
        self.__left_areas -= 1
        if self.__left_areas == 0 or obj['data']['y'] >= 1000:#All villages on map have been searched
            print('Done with villages')
            self.__command = { 'type': 'Ranking/getTribeRanking', 'offset': 0 }
            time.sleep(1.0)
            self.__client.emit('Ranking/getTribeRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': 0, 'order_by': 'rank', 'order_dir': 0, 'query': '' }, self.__ontribes)
        else:
            (x, y) = (obj['data']['x'] + 50, obj['data']['y'])
            print('Square: { x:', x, ', y:', y, '}')
            if x >= self.__endX:
                (x, y) = (self.__startX, y + 50)
            self.__command = { 'type': 'Map/getVillagesByArea', 'data': {'x': x, 'y': y}}
            time.sleep(1.0)
            self.__client.emit('Map/getVillagesByArea', { 'x': x, 'y': y, 'width': 50, 'height': 50, 'character_id': self.__player_id }, self.__onvillages)

    def __ontribes(self, obj):
        '''Callback for tribes
        '''
        if obj['type'] == 'System/error' or obj['type'] == 'Exception/ErrorException':
            time.sleep(120.0)
            self.__state = False
            self.__client.restart()
            self.start()
            return
        elif obj['type'] == 'Achievement/progress':
            print('Ignoring:', obj['type'])
            return

        if obj['type'] != 'Ranking/tribe':
            print(obj)
            if self.__command['type'] == 'Ranking/getTribeRanking':
                self.__client.emit('Ranking/getTribeRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': self.__command['offset'], 'order_by': 'rank', 'order_dir': 0, 'query': '' }, self.__ontribes)
            else:
                self.__client.emit('Ranking/getCharacterRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': 0, 'order_by': 'rank', 'order_dir': 0, 'query': ''}, self.__onplayers)
            return
        total = obj['data']['total']
        tribes = obj['data']['ranking']
        offset = obj['data']['offset']
        for tribe in tribes:
            self.__data['tribes'].append(tribe)
        if len(self.__data['tribes']) >= total:
            print('Done with tribes')
            self.__command = { 'type': 'Ranking/getCharacterRanking', 'offset': 0 }
            time.sleep(1.0)
            self.__client.emit('Ranking/getCharacterRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': 0, 'order_by': 'rank', 'order_dir': 0, 'query': ''}, self.__onplayers)
        else:
            print('Tribes offset:', offset)
            self.__command = { 'type': 'Ranking/getTribeRanking', 'offset': offset + 150 }
            time.sleep(1.0)
            self.__client.emit('Ranking/getTribeRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': offset + 150, 'order_by': 'rank', 'order_dir': 0, 'query': '' }, self.__ontribes)

    def __onplayers(self, obj):
        '''Callback for players
        '''
        if obj['type'] == 'System/error' or obj['type'] == 'Exception/ErrorException':
            time.sleep(120.0)
            self.__state = False
            self.__client.restart()
            self.start()
            return
        elif obj['type'] == 'Achievement/progress':
            print('Ignoring:', obj['type'])
            return
            
        if obj['type'] != 'Ranking/character':
            print(obj)
            if self.__command['type'] == 'Ranking/getPlayerRanking':
                self.__client.emit('Ranking/getCharacterRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': self.__command['offset'], 'order_by': 'rank', 'order_dir': 0, 'query': ''}, self.__onplayers)
                return
        total = obj['data']['total']
        players = obj['data']['ranking']
        offset = obj['data']['offset']
        for player in players:
            self.__data['players'].append(player)
        if len(self.__data['players']) >= total:
            print('Finish with players')
            self.__finish()
        else:
            print('Players offset:', offset)
            self.__command = { 'type': 'Ranking/getCharacterRanking', 'offset': offset + 150 }
            time.sleep(1.0)
            self.__client.emit('Ranking/getCharacterRanking', { 'area_id': None, 'area_type': 'world', 'count': 150, 'offset': offset + 150, 'order_by': 'rank', 'order_dir': 0, 'query': ''}, self.__onplayers)
    
    def __finish(self):
        '''Finish taking dump update db
        '''
        self.__client.disconnect()
        self.__state = True
        
        tribes = self.__data['tribes']
        players = self.__data['players']
        villages = self.__data['villages']
        print('Starting updating databases')
        conn = pgdb.connect(database='tw2-stats', user='tw2-stats', host='127.0.0.1', password=PASSWORD)
        cur = conn.cursor()
        cur.execute(update_world, (len(tribes), len(players), len(villages), self.__world))
        conn.commit()
        cur.close()
        conn.close()

        conn = pgdb.connect(database=self.__world, user='tw2-stats', host='127.0.0.1', password=PASSWORD)
        cur = conn.cursor()
        #Update tribes
        query = 'UPDATE tribes SET active = false WHERE true;'
        cur.execute(query)
        for tribe in tribes:
            params = (
                tribe['tribe_id'], tribe['name'], tribe['name'].lower(), tribe['tag'], True, tribe['members'], tribe['points'], tribe['bash_points_off'], tribe['bash_points_def'], tribe['bash_points_total'], tribe['rank'], tribe['villages'], tribe['victory_points'], self.__timestamp,
                tribe['tribe_id'], tribe['name'], tribe['name'].lower(), tribe['tag'], True, tribe['members'], tribe['points'], tribe['bash_points_off'], tribe['bash_points_def'], tribe['bash_points_total'], tribe['rank'], tribe['villages'], tribe['victory_points'], self.__timestamp
            )
            cur.execute(upsert_tribes, params)

        #Update players
        for player in players:
            tid = 0
            if player['tribe_id']:
                tid = player['tribe_id']
            params = (
                player['character_id'], player['name'], player['name'].lower(), tid, player['villages'], player['points'], player['bash_points_off'], player['bash_points_def'], player['bash_points_total'], player['rank'], player['victory_points'], self.__timestamp,
                player['character_id'], player['name'], player['name'].lower(), tid, player['villages'], player['points'], player['bash_points_off'], player['bash_points_def'], player['bash_points_total'], player['rank'], player['victory_points'], self.__timestamp
            )
            cur.execute(upsert_players, params)
        
        #Update villages
        for village in villages:
            tid = 0
            pid = 0
            if village['tribe_id']:
                tid = village['tribe_id']
            if village['character_id']:
                pid = village['character_id']
            params = (
                village['id'], village['name'], village['name'].lower(), pid, tid, village['x'], village['y'], village['points'], village['province_name'], self.__timestamp,
                village['id'], village['name'], village['name'].lower(), pid, tid, village['x'], village['y'], village['points'], village['province_name'], self.__timestamp,
            )
            cur.execute(upsert_villages, params)

        conn.commit()
        cur.close()
        conn.close()
        print('Done updating databases')

        #Recluster database
        conn = pgdb.connect(database=self.__world, user='tw2-stats', host='127.0.0.1', password=PASSWORD)
        conn.set_isolation_level(pgdb.extensions.ISOLATION_LEVEL_AUTOCOMMIT)
        cur = conn.cursor()
        cur.execute('CLUSTER;')
        conn.commit()
        cur.close()
        conn.close()

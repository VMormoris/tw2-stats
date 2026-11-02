from sqlite3 import connect
from typing import Callable
import socketio
import time

EXIT_FAILURE = 1
EXIT_SUCCESS = 0

class tw2Client:
    '''Class that handles a connection with tw2 servers
    '''

    #__TYPES = ['Login/success', 'Authentication/characterSelected', 'Map/villageData', 'Ranking/character', 'Ranking/tribe', 'System/error', 'System/welcome', 'GameGuard/getInfo']#List of accepted types

    def __init__(self, url: str = '', token: str=''):
        '''Initializes the client and calls connect on the specified url

        Parameters:
            url (str): String containing the url of the server that will be connecting to
        '''
        self.__sio = socketio.Client()#socket.io object, one per client
        self.__id = 1
        self.__tokenEmit = token
        self.__callback = None
        self.__url = url
        if(not(url=='')):
            self.connect(url)

    def emit(self, type: str, data: dict, callback: Callable[[dict], None]):
        '''Emits message to tw2 - server
        Parameters:
            type (str): "Code" for the apropriate request
            data (dict): The javascript object in dictinary form
            callback (function): Callback function to handle the response
        '''
        #Set actual users callbacks
        self.__callback = callback
        self.callbacks()

        #Check if tokenEmit exist and if it set the apropriate fields
        if not(self.__tokenEmit == ''):
            data['tokenEmit'] = self.__tokenEmit
            data['userAgent'] = 'broswer'    
        
        #Emit the message and increment the id by one
        self.__sio.emit('msg', {
            'type': type,
            'data': data,
            'id': self.__id,
            'headers': { 'traveltimes': [['broswer_send', round(time.time() * 1000)]]}
        })
        self.__id += 1

    def connect(self, url: str, deadline: float = float('inf')):
        '''Create a connection with the given tw2 - server

        Parameters: 
            url (str): String containing the url of the specified server
            deadline (float): time.monotonic() after which it stops trying
        '''
        self.__url = url
        while time.monotonic() < deadline:
            try:
                self.__sio.connect(url, transports = ["websocket", "polling", "polling-jsonp", "polling-xhr"])
                return
            except socketio.exceptions.ConnectionError:
                time.sleep(60.0)

    def disconnect(self):
        '''Disconects from the server that was last connected to
        '''
        self.__id = 1
        self.__sio.disconnect()
        self.__sio = socketio.Client()

    def callbacks(self):
        '''All callbacks functions because:
            https://github.com/miguelgrinberg/python-socketio/issues/390
        '''
        @self.__sio.event
        def disconnect():
            print('Disconnecting')

        @self.__sio.on('msg')
        def on_message(obj: dict):
            self.__callback(obj)


    def __consume(self, obj: dict):
        '''Consumes the given response javascript object (dictionary)

        Parameters:
            obj (dict): Dictionary of the response object
        '''

        if obj['type'] == 'Login/success':

            pass
        elif obj['type'] == 'Authentication/characterSelected':
            pass
        else:#Error on response
            self.disconnect()
            print(obj)
            exit(EXIT_FAILURE)
    
    def setEmitToken(self, token: str):
        '''Set the emit token for the connection with the current world

        Parameters:
            token (str): The emit token that we want to use for the future emits on this server
        '''
        self.__tokenEmit = token

    def restart(self, deadline: float = float('inf')):
        self.disconnect()
        self.connect(self.__url, deadline)

    def totalEmits(self):
        return self.__id - 1
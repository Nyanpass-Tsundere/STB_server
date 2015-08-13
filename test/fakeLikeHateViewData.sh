#!/bin/bash

function press() {
	data=`curl http://stb.dd-han.tw/SmarttvWebServiceApi/GetChannelStatus?msoid=1\&channelnum=${1}`
	
	channel=`echo $data | sed s/.*channelname\"\:\ \"//g | sed s/\".*//g`
	program=`echo $data | sed s/.*programname\"\:\ \"//g | sed s/\".*//g`
	programTime=`echo $data | sed s/.*programstarttime\"\:\ \"//g | sed s/\".*//g`
		
	if [ "$channel" != "" ]; then
		echo "Channel=$channel program=$program programTime=$programTime"
		
		curl "http://stb.dd-han.tw/api/sentStatus?UID=$2&Channel=${channel}&Program=${program}&Time=${programTime}&Status=$3"
	else
		echo "empty Channel"
	fi
}

if [ "$1" == "-n" ] ;then

	for i in $(seq 1 200);do
		press $i 99999 0
	done

else	
	for i in $(seq 1 $1);do
		sentUID=`echo $(( $RANDOM % 10))`
		sentChannel=`echo $(( $RANDOM % 100))`
		sentStatus=`echo $(( $RANDOM % 3 - 1))`
		
		echo "亂按"
		
		press $sentChannel $sentUID $sentStatus
	done
fi

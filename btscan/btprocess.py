#!/usr/bin/env python3

# Based on ReachView code from Egor Fedorov (egor.fedorov@emlid.com)
# Updated for Python 3.6.8 on a Raspberry  Pi

import os
import signal
import io
import subprocess
import datetime
import time
import sys
import re
import requests
import json
import logging
from optparse import OptionParser

FOLDER = os.path.abspath(__file__)
PROGRESS = "/in.progress"
WEBSITE = "https://www.btsigmon.com"
SCRIPT = "/php/upload_test.php"
CHUNK_SIZE = 20

LOG_LEVEL = logging.INFO
LOG_FILE = "/var/log/syslog"
LOG_FORMAT = "%(asctime)s %(levelname)s [%(module)s] %(message)s"

def construct_json_entries(array, index, offset):
    json_list = []
    length = index + offset
    while index < length:
        values = array[index].split("|")
        addr = values[0].split()
        sstr = values[1].split()
        dtime = values[2].split()
        json_list.append({
            "mac" : addr[0],
            "datetime" : dtime[0]+ " " +dtime[1],
            "rssi" : sstr[0]
            })
        index += 1
    return json_list
    
def request_chunk_entries(json_list, auth, experiment):
    json_data = {
        "auth" : auth,
        "experiment" : experiment,
        "entries" : json_list
    }
    header = {
        "Content-type": "application/json; charset=UTF-8"
    }
    req = requests.post(WEBSITE + SCRIPT, json=json_data, headers=header)
    if(req.status_code != 200):
        print("\n    ERROR: Response not successful (" +str(req.status_code)+ ")")
        exit("\nProgram aborted due to HTTP error")
    resp = req.json()
    if(resp["success"] == "fail"):
        print("\n        * ERROR [" +resp["error"]+ "]\n          " +resp["message"])
    if(resp["success"] == "ok"):
        dupes = 0
        for mac in resp["macs"]:
            if(mac == "duplicate"):
                dupes += 1
        print("        * SUCCESS Entries inserted: " +str(resp["length"] - dupes)+ ", Duplicates: " +str(dupes))
    

def handle_signal(sig, frame):
    try:
        exit("\nExiting ({})".format(sig)) 
    except Exception as e:
        logging.info("Signal handler error: {}".format(type(e).__name__))
    exit("\nExiting ({})".format(sig))

if __name__ == "__main__":
    parser = OptionParser()
    parser.add_option("-n", "--name", action="store", dest="name", help="Name location where experiment happened")
    parser.add_option("-t", "--time", action="store", dest="time", help="Start time of experiment in SQL format (Y-d-m H:M:S)")
    parser.add_option("-m", "--mac", action="store", dest="mac", help="Bluetooth MAC address of scanning device")
    parser.add_option("-l", "--lat", action="store", dest="lat", help="GPS Latitude of scanning device")
    parser.add_option("-L", "--lng", action="store", dest="lng", help="GPS Longitude of scanning device")
    parser.add_option("-p", "--pkey", action="store", dest="pkey", help="Passkey for database insertion from PHP")
    parser.add_option("-q", "--quiet", action="store_false", dest="verbose", default=True, help="Disable console output")
    (options, args) = parser.parse_args()

    name = "Default Location"
    times = "2000-01-01 01:23:45"
    mac = "01:23:45:67:89:AB"
    lat = 0.0
    lng = 0.0
    pkey = "empty"
    experiment = None

    logging.basicConfig(filename=LOG_FILE, format=LOG_FORMAT, level=LOG_LEVEL)

    signal.signal(signal.SIGHUP, handle_signal)
    signal.signal(signal.SIGINT, handle_signal)
    signal.signal(signal.SIGQUIT, handle_signal)
    signal.signal(signal.SIGTERM, handle_signal)

    if (len(sys.argv) > 1):
        error = "File Not Exists"
        try:
            with open(sys.argv[1], "r") as infile:
                if (options.name):
                    name = options.name
                if (options.time):
                    times = options.time
                else:
                    try:
                        line = infile.readline()
                        if (re.match("time ", line, re.I) is None):
                            exit("Missing timestamp from 1st line of file")
                        times = re.search("[0-9]{4}\-[0-9]+\-[0-9]+\040[0-2][0-9]:[0-9]{2}:[0-9]{2}", line)
                        if(times):
                            times = times.group(0)
                        else:
                            exit("Invalid time format in file")
                    except:
                        error = "Timestamp Read"
                        raise Exception
                if (options.mac):
                    mac = options.mac
                else:
                    try:
                        out = subprocess.check_output("hciconfig hci0", shell=True, universal_newlines=True)
                        regex = re.search("([a-fA-F0-9]{2}:){5}([a-fA-F0-9]{2})", out)
                        mac = regex.group(0)
                    except:
                        error = "Hciconfig BD Address"
                        raise Exception
                if (options.lat):
                    lat = options.lat
                else:
                    try:
                        line = infile.readline()
                        if (re.match("lat ", line, re.I) is None):
                            exit("Missing latitude from 2nd line of file")
                        lat = re.search("\-?1?[0-9]?[0-9]\.[0-9]{2,12}", line)
                        if(lat):
                            lat = lat.group(0)
                        else:
                            exit("Invalid lat format in file")
                    except:
                        error = "Latitude Read"
                        raise Exception
                if (options.lng):
                    lng = options.lng
                else:
                    try:
                        line = infile.readline()
                        if (re.match("lng ", line, re.I) is None):
                            exit("Missing longitude from 3rd line of file")
                        lng = re.search("\-?1?[0-9]?[0-9]\.[0-9]{2,12}", line)
                        if(lng):
                            lng = lng.group(0)
                        else:
                            exit("Invalid lng format in file")
                    except:
                        error = "Longitude Read"
                        raise Exception
                infile.close()
        except Exception as e:
            exit("Error parsing options for filename \"" +sys.argv[1]+ " : " +error)
    else:
        exit("Filename argument not specified")
        
    print(" -Name: " +name)
    print(" -Time: " +times)
    print(" -MAC: " +mac)
    print(" -Latitude: " +lat)
    print(" -Longitude: " +lng)
    if (options.pkey):
        pkey = options.pkey
    else:
        print("\n  WARNING: Passkey has not been supplied (use -p option)")
    answer = input("\nWould you like to continue with upload (Y/N)?\n")
    if (re.match("[yY]", answer, re.I) is None):
        exit("Program aborted due to user input")
        
    if(options.verbose):
        print("\n    \\\\\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\///")
        print("    \\\\\u00b7------------------------------------------------\u00b7//")
        print("    \\>|                                                |</")
        print("    >>|       Commencing upload to BTSIGMON.COM        |<<")
        print("    />|                                                |<\\")
        print("    //\u00b7------------------------------------------------\u00b7\\\\")
        print("    ///\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\/\\\\\\")
        
    data = {
        "auth" : pkey,
        "name" : name,
        "datetime" : times,
        "mac" : mac,
        "lat" : lat,
        "lng" : lng
    }
    header = {
        "Content-type": "application/json; charset=UTF-8"
    }
    print("\n    - Requesting confirmation to enter Experiment information to database")
    req = requests.post(WEBSITE + SCRIPT, json=data, headers=header)
    print("    - Response received")
    if(req.status_code != 200):
        print("    - Response not successful (" +str(req.status_code)+ ")")
        exit("\nProgram aborted due to HTTP error")
    print("    - Response successful!")
    resp = req.json()
    if(resp["success"] == "fail"):
        exit("\n    ERROR [" +resp["error"]+ "] " +resp["message"])
    if(resp["success"] == "ok"):
        experiment = resp["experiment"]
        print("    - Experiment entry succeeded with ID " +str(experiment))
        if(options.verbose):
            print("           Name -> " +resp["name"])
            print("       Datetime -> " +resp["datetime"])
            print("            MAC -> " +resp["mac"])
            print("       Latitude -> " +resp["lat"])
            print("      Longitude -> " +resp["lng"])
            
    print("\n    - Parsing file for RSSI Values")
    with open(sys.argv[1], "r") as f:
        rssi = f.readlines()
        length = len(rssi)
        index = 3
        
        print("    - Entries to process: " +str(length - index))
        
        while index < length:
            entries = None
            print("      *** Preparing JSON for data chunk " +str(index//CHUNK_SIZE+1)+ " out of " +str(length//CHUNK_SIZE if length%CHUNK_SIZE==0 else length//CHUNK_SIZE+1))
            if((index + CHUNK_SIZE) > length):
                entries = construct_json_entries(rssi, index, length - index)
            else:
                entries = construct_json_entries(rssi, index, CHUNK_SIZE)
            print("       ** Sending JSON data as " +str(len(entries))+ " entries to server")
            request_chunk_entries(entries, pkey, experiment)
            index += CHUNK_SIZE
            
        f.close()

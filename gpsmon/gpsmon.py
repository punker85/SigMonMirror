#!/usr/bin/env python3

import os
import signal
import inotify.adapters
import datetime
import time
import re
import logging

FOLDER = "/home/pi/obexpush"
FOLDER_LOG = FOLDER + "/log"
COORD = "/var/gps"
LAT = COORD + "/lat"
LNG = COORD + "/lng"

LOG_LEVEL = logging.INFO
LOG_FILE = "/var/log/syslog"
LOG_FORMAT = "%(asctime)s %(levelname)s [%(module)s] %(message)s"

def set_coord(axis, value):
    try:
        with open(axis, "w") as f:
            f.write(value + "\n")
            f.close()
    except Exception as e:
        logging.info("File write error: {0}, Value: {1}".format(type(e).__name__, value))

def scan_file(file):
    try:
        with open(file, "r") as f:
            text = f.read()
            html = re.findall("(-?\d+\.\d{7})", text)
            if (len(html) < 12):
                html = re.findall("(-?\d+\.\d+)", text)
            lat = html[0]
            lng = html[1]
            f.close()
            return lat, lng
    except Exception as e:
        logging.info("Parsing file contents error: {0}, File: {1}".format(type(e).__name__, file))
        try:
            with open(FOLDER_LOG +"/"+ datetime.datetime.now().time() +".html", "w") as f:
                f.write(html + "\n")
                f.close()
        except:
            print("error logging error")
        return "0.0", "0.0"

def delete_file(file):
    try:
        if os.path.exists(file):
            os.remove(file)
            return True
        else:
            logging.info("Delete: File does not exist")
            return False
    except Exception as e:
        logging.info("Deleting file error: {0}, File: {1}".format(type(e).__name__, file))
        return False

def log_file(folder, lat, lng):
    try:
        with open(folder + "/gps.log", "a") as f:
            f.write(datetime.datetime.now().strftime("%Y-%m-%d %H:%M:%S") + " -- Lat(" +lat+ "), Lng(" +lng+ ")\n")
            f.close()
    except Exception as e:
        logging.info("Logging file error: {0}, File: {1}, Lat: {2}, Lng: {3}".format(type(e).__name__, file, lat, lng))

def handle_signal(sig, frame):
    if (sig == signal.SIGINT):
        logging.info("Received SIGINT, Exiting script")
    if (sig == signal.SIGQUIT):
        logging.info("Received SIGQUIT, Exiting script")
    if (sig == signal.SIGTERM):
        logging.info("Received SIGTERM, Exiting script")
    raise SystemExit("  Exiting ({})".format(sig))
    return

if __name__ == "__main__":
    logging.basicConfig(filename=LOG_FILE, format=LOG_FORMAT, level=LOG_LEVEL)
    logging.info("Starting GPS Monitor...")

    signal.signal(signal.SIGINT, handle_signal)
    signal.signal(signal.SIGQUIT, handle_signal)
    signal.signal(signal.SIGTERM, handle_signal)

    if not os.path.exists(FOLDER):
        os.makedirs(FOLDER, 0o755)
    if not os.path.exists(FOLDER_LOG):
        os.makedirs(FOLDER_LOG, 0o755)
    if not os.path.exists(COORD):
        os.makedirs(COORD, 0o755)

    for root, dirs, files in os.walk(FOLDER):
        for f in files:
            if (root == FOLDER):
                os.remove(os.path.join(root, f))

    mon = inotify.adapters.Inotify()
    mon.add_watch(FOLDER)

    for event in mon.event_gen():
        if event is not None:
            if "IN_CREATE" in event[1]:
                logging.info("New file: \"{}\"".format(event[3]))
            if "IN_CLOSE_WRITE" in event[1]:
                time.sleep(1)
                lat, lng = scan_file(event[2] +"/"+ event[3])
                set_coord(LAT, lat)
                set_coord(LNG, lng)
                deleted = delete_file(event[2] +"/"+ event[3])
                if (deleted):
                    log_file(FOLDER_LOG, lat, lng)
                else:
                    logging.info("Wrong file format: {0}".format(event[3]))

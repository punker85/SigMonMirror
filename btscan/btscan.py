#!/usr/bin/env python3

# Based on ReachView code from Egor Fedorov (egor.fedorov@emlid.com)
# Updated for Python 3.6.8 on a Raspberry  Pi

import os
import signal
import io
import datetime
import time
import pexpect
import subprocess
import sys
import logging

FOLDER = "/home/pi/btmon_output"
INPUT = "/home/pi/btmon_output.txt"
LAT = "/var/gps/lat"
LNG = "/var/gps/lng"

LOG_LEVEL = logging.INFO
LOG_FILE = "/var/log/syslog"
LOG_FORMAT = "%(asctime)s %(levelname)s [%(module)s] %(message)s"

rfkill = None
btctl = None
btmon = None
btsort = None

def start_scan():
    try:
        btctl.sendline("scan on")
    except Exception as e:
        logging.info("Bluez scan-on error: {}".format(type(e).__name__))
        if((btctl is not None) and btctl.isalive()):
            btctl.kill(9)
        if((btmon is not None) and (btmon.poll() is None)):
            btmon.kill()
        raise SystemExit("Exiting (bluetoothctl scan on failed)")

def stop_scan():
    try:
        btctl.sendline("scan off")
        time.sleep(0.5)
        btctl.sendline("exit")
        try:
            btctl.expect(pexpect.EOF, timeout=1)
        except TIMEOUT:
            logging.info("btctl expect EOF timeout")
        btctl.close(force=True)
        btmon.terminate()
        try:
            btmon.wait(1)
        except TimeoutExpired:
            btmon.kill()
            logging.info("btmon terminate timeout")
    except Exception as e:
        if(btctl.isalive()):
            btctl.kill(9)
        if(btmon.poll() is None):
            btmon.kill()
        logging.info("Bluez scan-off error: {}".format(type(e).__name__))

def sort_output():
    try:
        btsort = subprocess.Popen("sort -k 4 " +INPUT+ " | uniq > " +FOLDER+ "/" +datetime.datetime.now().strftime("%b%d-%H%M")+ ".txt", shell=True, stdout=subprocess.DEVNULL)
        try:
            btsort.wait(10)
        except TimeoutExpired:
            btsort.kill()
            logging.info("sort process timeout")
    except Exception as e:
        if((btsort is not None) and (btsort.poll() is None)):
            btsort.kill()
        logging.info("sort error: {}".format(type(e).__name__))

def handle_signal(sig, frame):
    try:
        stop_scan()
        sort_output()
    except Exception as e:
        logging.info("Signal handler error: {}".format(type(e).__name__))
    time.sleep(0.25)
    if os.path.exists(INPUT):
        os.remove(INPUT)
    logging.info("Stopping Bluetooth packet monitoring")
    raise SystemExit("Exiting ({})".format(sig))
    return

#def kill_all(sig, frame):
#    if(btctl.isalive()):
#        btctl.kill(9)
#    if(btmon.poll() is None):
#        btmon.kill()
#    if(btsort.poll() is None):
#        btsort.kill()
#    raise SystemExit("Exiting (SIGKILL)")
#    return

if __name__ == "__main__":
    logging.basicConfig(filename=LOG_FILE, format=LOG_FORMAT, level=LOG_LEVEL)
    logging.info("Starting Bluetooth packet monitoring")

    signal.signal(signal.SIGHUP, handle_signal)
    signal.signal(signal.SIGINT, handle_signal)
    signal.signal(signal.SIGQUIT, handle_signal)
    signal.signal(signal.SIGTERM, handle_signal)
    #signal.signal(signal.SIGKILL, kill_all)

    if not os.path.exists(FOLDER):
        os.makedirs(FOLDER, 0o755)

    if os.path.exists(INPUT):
        os.remove(INPUT)

    time.sleep(0.25)

    try:
        rfkill = subprocess.check_output("rfkill unblock bluetooth", shell=True)
        logging.info("rfkill unblock bluetooth: {}".format(rfkill))
    except Exception as e:
        logging.info("rfkill error: {}".format(type(e).__name__))

    try:
        btctl = pexpect.spawn("bluetoothctl", echo=False, encoding='utf-8')
        logging.info("bluetoothctl started")
    except Exception as e:
        if((btctl is not None) and btctl.isalive()):
            btctl.kill(9)
        logging.info("bluetoothctl error: {}".format(type(e).__name__))
        raise SystemExit("Exiting (bluetoothctl process did not start)")

    try:
        btmon = subprocess.Popen("btmon", shell=True, stdout=subprocess.DEVNULL)
        logging.info("btmon started")
    except Exception as e:
        if((btctl is not None) and btctl.isalive()):
            btctl.kill(9)
        if((btmon is not None) and (btmon.poll() is None)):
            btmon.kill()
        logging.info("btmon error: {}".format(type(e).__name__))
        raise SystemExit("Exiting (btmon process did not start)")

    time.sleep(0.25)
    start_scan()

    while (True):
        time.sleep(60)

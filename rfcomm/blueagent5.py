#!/usr/bin/python

# blueagent5.py
# Dependencies: python-gobject (install with e.g. 'sudo apt-get install python-gobject' on Raspian
# Author: Douglas Otwell
# This software is released to the public domain

# The Software is provided "as is" without warranty of any kind, either express or implied, 
# including without limitation any implied warranties of condition, uninterrupted use, 
# merchantability, fitness for a particular purpose, or non-infringement.

import time
import sys
import dbus
import dbus.service
import dbus.mainloop.glib
from gi.repository import GObject as gobject
from gi.repository import GLib
import logging
from optparse import OptionParser

SERVICE_NAME = "org.bluez"
AGENT_IFACE = SERVICE_NAME + ".Agent1"
ADAPTER_IFACE = SERVICE_NAME + ".Adapter1"
DEVICE_IFACE = SERVICE_NAME + ".Device1"
PLAYER_IFACE = SERVICE_NAME + ".MediaPlayer1"

LOG_LEVEL = logging.INFO
LOG_FILE = "/var/log/syslog"
#LOG_LEVEL = logging.DEBUG
#LOG_FILE = "log.txt"
LOG_FORMAT = "%(asctime)s %(levelname)s [%(module)s] %(message)s"

def getManagedObjects():
    bus = dbus.SystemBus()
    manager = dbus.Interface(bus.get_object("org.bluez", "/"), "org.freedesktop.DBus.ObjectManager")
    return manager.GetManagedObjects()

def findAdapter():
    objects = getManagedObjects();
    bus = dbus.SystemBus()
    for path, ifaces in objects.items():
        adapter = ifaces.get(ADAPTER_IFACE)
        if adapter is None:
            continue
        obj = bus.get_object(SERVICE_NAME, path)
        return dbus.Interface(obj, ADAPTER_IFACE)
    raise Exception("Bluetooth adapter not found")

class Rejected(dbus.DBusException):
    _dbus_error_name = "org.bluez.Error.Rejected"

class BlueAgent(dbus.service.Object):
    AGENT_PATH = "/blueagent5/agent"
    CAPABILITY = "DisplayYesNo"
    pin_code = None
    passkey = "000000"
    devices = ["/org/bluez/hci0/dev_88_83_22_A1_8F_39", "/org/bluez/hci0/dev_00_5B_94_28_C3_EE", "/org/bluez/hci0/dev_94_76_B7_2D_00_10"]

    def __init__(self, pin_code):
        dbus.service.Object.__init__(self, dbus.SystemBus(), BlueAgent.AGENT_PATH)
        self.pin_code = pin_code

        logging.basicConfig(filename=LOG_FILE, format=LOG_FORMAT, level=LOG_LEVEL)
        logging.info("Starting with PIN [{0}], Passkey [{1}]".format(self.pin_code, self.passkey))

    @dbus.service.method(AGENT_IFACE, in_signature="os", out_signature="")
    def DisplayPinCode(self, device, pincode):
        logging.info("DisplayPinCode invoked: [{0}] [{1}]".format(device, pincode))

    @dbus.service.method(AGENT_IFACE, in_signature="ouq", out_signature="")
    def DisplayPasskey(self, device, passkey, entered):
        logging.info("DisplayPasskey invoked: [{0}] [{1}] [{2}]".format(device, passkey, entered))

    @dbus.service.method(AGENT_IFACE, in_signature="o", out_signature="s")
    def RequestPinCode(self, device):
        logging.info("RequestPinCode invoked:  [{}]".format(device))
        self.trustDevice(device)
        return self.pin_code

    @dbus.service.method(AGENT_IFACE, in_signature="ou", out_signature="")
    def RequestConfirmation(self, device, passkey):
        logging.info("Passkey confirmation request: [{0}] [{1}]".format(device, passkey))
        try:
            self.devices.index(device)
            logging.info("  Device authorized!")
            self.passkey = passkey
            self.trustDevice(device)
            return
        except:
            logging.info("  Device not authorized!")
            raise Rejected("Device rejected.")

    @dbus.service.method(AGENT_IFACE, in_signature="os", out_signature="")
    def AuthorizeService(self, device, uuid):
        logging.info("BlueAgent AuthorizeService method invoked: [{0}] [{1}]".format(device, uuid))
        try:
            self.devices.index(device)
            logging.info("  Service authorized!")
            return
        except:
            logging.info("  Service not authorized!")
            raise Rejected("Service rejected.")

    @dbus.service.method(AGENT_IFACE, in_signature="o", out_signature="u")
    def RequestPasskey(self, device):
        logging.info("RequestPasskey returns passkey: [{0}] [{1}]".format(device, self.passkey))
        return dbus.UInt32(self.passkey)

    @dbus.service.method(AGENT_IFACE, in_signature="o", out_signature="")
    def RequestAuthorization(self, device):
        logging.info("Just-Works pairing request: [{}]".format(self.device))
        raise Rejected("Incoming pairing request rejected!")

    @dbus.service.method(AGENT_IFACE, in_signature="", out_signature="")
    def Cancel(self):
        logging.info("BlueAgent pairing request canceled from device [{}]".format(self.device))

    def trustDevice(self, path):
        bus = dbus.SystemBus()
        device_properties = dbus.Interface(bus.get_object(SERVICE_NAME, path), "org.freedesktop.DBus.Properties")
        device_properties.Set(DEVICE_IFACE, "Trusted", True)

    def registerAsDefault(self):
        bus = dbus.SystemBus()
        manager = dbus.Interface(bus.get_object(SERVICE_NAME, "/org/bluez"), "org.bluez.AgentManager1")
        manager.RegisterAgent(BlueAgent.AGENT_PATH, BlueAgent.CAPABILITY)
        manager.RequestDefaultAgent(BlueAgent.AGENT_PATH)

    def startPairing(self):
        bus = dbus.SystemBus()
        adapter_path = findAdapter().object_path
        adapter = dbus.Interface(bus.get_object(SERVICE_NAME, adapter_path), "org.freedesktop.DBus.Properties")
        adapter.Set(ADAPTER_IFACE, "Discoverable", True)
        
        logging.info("BlueAgent is waiting to pair with device")
        
bus = None

if __name__ == "__main__":
    pin_code = "0000"
    parser = OptionParser()
    parser.add_option("-p", "--pin", action="store", dest="pin_code", help="PIN code to pair with", metavar="PIN")
    (options, args) = parser.parse_args()

    # use the pin code if provided
    if (options.pin_code):
        pin_code = options.pin_code

    dbus.mainloop.glib.DBusGMainLoop(set_as_default=True)

    agent = BlueAgent(pin_code)
    agent.registerAsDefault()
    agent.startPairing()

    mainloop = GLib.MainLoop()
    mainloop.run()


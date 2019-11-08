#!/bin/sh -e
# BlueAgent5 setup
/usr/local/bin/bluetoothctl power on
/bin/hciconfig hci0 piscan
/bin/hciconfig hci0 encrypt
/bin/hciconfig hci0 sspmode 1
# end setup

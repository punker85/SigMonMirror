#!/bin/bash
# Bluetooth Scanning Service Installation
cp btscan.service /etc/systemd/system/btscan.service
cp btscan.py /usr/local/bin/btscan.py

chmod 755 /usr/local/bin/btscan.py
ln -s /usr/local/bin/btscan.py /usr/local/bin/btscan

systemctl enable btscan
# end installation

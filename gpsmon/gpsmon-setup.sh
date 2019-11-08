#!/bin/bash
# GPS File Monitor Service Installation
cp gpsmon.service /etc/systemd/system/gpsmon.service
cp obexpush.service /etc/systemd/system/obexpush.service
cp gpsmon.py /usr/local/bin/gpsmon.py

chmod 755 /usr/local/bin/gpsmon.py
ln -s /usr/local/bin/gpsmon.py /usr/local/bin/gpsmon

systemctl enable gpsmon
systemctl enable obexpush
# end installation

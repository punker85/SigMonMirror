#!/bin/bash
# RFCOMM Terminal Service installation
cp ba5-setup.sh /usr/local/bin/ba5-setup.sh
cp blueagent5.py /usr/local/bin/blueagent5.py
cp blueagent5.service /etc/systemd/system/blueagent5.service
cp rfcomm.service /etc/systemd/system/rfcomm.service

chmod 755 /usr/local/bin/ba5-setup.sh
chmod 755 /usr/local/bin/blueagent5.py
ln -s /usr/local/bin/blueagent5.py /usr/local/bin/blueagent5

systemctl enable blueagent5
systemctl enable rfcomm
# end installation
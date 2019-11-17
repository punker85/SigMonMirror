# Bluetooth Scanning Service installation

## btscan will capture local Bluetooth device signals using piscan mode on HCI and btmon program to capture packets
1. Run the shell script `btscan-setup.sh`
* Files will be copied to their required destinations on the Raspberry Pi
* Systemd service will be enabled to run at startup

2. Reboot the Pi
* The Bluetooth controller will begin scanning immediately when the bluetooth daemon finishes initialization
  * If you wish to stop this from happening, run `systemctl disable btscan`
  * Then, you may start and stop the service with `systemctl start btscan` and `systemctl stop btscan` respectively
* Whenever the service is stopped or the process is terminated (during shutdown, reboot, etc.), a file is created in the program's FOLDER directory
  * Any Bluetooth signal information is logged in the file as one line per device/time/rssi pairing
  * Additionally, GPS coordinates will be prepended to the file from data kept in `/var/gps/lat` and `/var/gps/lng`
  

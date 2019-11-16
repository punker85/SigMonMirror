# GPS File Monitor setup on Raspbian OS

## ObEx Push Profile server with Python script to extract data from Google Maps Share Location file
1. Ensure the BlueZ (version 5.51) systemd service is running in compatibility mode
* Open the file `/lib/systemd/system/bluetooth.service` with `nano`
* Edit the line with `ExecStart` by adding `--compat` option like so:
```
ExecStart=/usr/local/libexec/bluetooth/bluetoothd --compat
```
* After this line, add `ExecStartPost` with `sdptool` to add Serial Port profile to the Bluetooth controller like so:
```
ExecStartPost=/usr/bin/sdptool add --channel=22 SP
```

2. Install dependencies: obexpushd and inotify
* Open terminal and run `sudo apt-get install obexpushd`
* Then, run `sudo pip3 install inotify`

3. Run the installation script to install obexpush.service and gpsmon.service units
* Install using `sh gpsmon-setup.sh`
  * This will copy files to appropriate directories and enable the systemd services
  * The `gpsmon.py` script will create all of the directory structures that it needs 

4. Reboot
* Reboot the RPi
* Open shell terminal and check systemd services with `systemctl list-unit-files`
  *Ensure that `obexpush.service` and `gpsmon.service` have `enabled` state
* Additionally, check the status of each service for error messages using:
```
systemctl status obexpush
```
 -and-
```
systemctl status gpsmon
```
  *If each service has a green light, then you are ready to send GPS .html files with Google Maps on your phone

5. Download/Open Google Maps on Android or iOS phone
* Download Google Maps if you do not have it on your phone
* Pair your phone with the RPi (assuming you have `blueagent5` installed from the /rfcomm/ folder)
* Drop a red balloon marker on your current location
  * Use the bullseye-target locator button to pan to your current GPS location
  * Zoom in on your blue-dot marker as much as possible for accuracy in the next step
  * Long-press on the center of your blue-dot marker to drop a red balloon marker
* Share the GPS location of that red balloon marker to the RPi
  * Click on the red balloon marker
  * In the new menu, choose Share or Share Place option
  * Pick the Bluetooth sub-option and choose your RPi device as the receiver
* Your phone should send .html file with coordinates inside that gpsmon.py will decode
  * Latitude and longitude will be respectively stored in `/var/gps/lat` and `/var/gps/lng` as a single-line value

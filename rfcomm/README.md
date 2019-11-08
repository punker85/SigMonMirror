# RFCOMM Server setup on Raspbian OS

## Serial Port Profile server socket connected to getty console
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

2. Run the installation script to install blueagent5.service and rfcomm.service units
* Install using `sh rfcomm-setup.sh`
  * This will copy files to appropriate directories and enable the systemd services
  * The `ba5-setup.sh` script will execute before `blueagent5` service starts in order to power on Bluetooth controller and set PISCAN AUTH ENCRYPT SSPMODE on `hci0`

3. Reboot
* Reboot the RPi
* Open shell terminal and check systemd services with `systemctl list-unit-files`
  *Ensure that `blueagent5.service` and `rfcomm.service` have `enabled` state
* Additionally, check the status of each service for error messages using:
```
systemctl status blueagent5
```
 -and-
```
systemctl status rfcomm
```
  *If each service has a green light, then you are ready to connect with your smart phone

4. Download Bluetooth serial port application on your Android or iOS phone
* Some usable programs for Android OS are `Serial Bluetooth Terminal` `BlueTerm` and `Bluetooth Terminal`
  * [Serial Bluetooth Terminal](https://play.google.com/store/apps/details?id=de.kai_morich.serial_bluetooth_terminal&hl=en_US)
  * [Bluetooth Terminal](https://play.google.com/store/apps/details?id=Qwerty.BluetoothTerminal&hl=en_US)
* Enable options to keep the screen on and add carriage returns when sending input
  * In Serial Bluetooth Terminal, Send options only need CR enabled
  * In Bluetooth Terminal, the only option is to send `\r\n`

5. Enter Bluetooth settings on your smart phone
* Start scanning for devices
* Hope that the RPi with the hostname of your choosing shows
* Pair with the RPi device and it should send 6-digit passkey
  *This automatically validates on the RPi agent server as long as phone Bluetooth MAC address is in list of `blueagent5.py` script

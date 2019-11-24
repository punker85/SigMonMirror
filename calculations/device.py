import sys
import time
import os.path
import sqlite3
import bluetoothctl
import json
import math

class Device:
    def __init__(self):
        self.mac = None
        self.dev_name = None
        self.rssi = 0
        self.dev_latitude = None
        self.dev_longitutde = None
        self.pi_discovery = None
        self.scan_date = None
        self.gps = []
        self.distance = None
        
    # set methods 
    def set_gps(self, long, lat):
        self.longitude = long
        self.latitude = lat
        self.gps.append(long)
        self.gps.append(lat)
    
    def set_mac(self, mac):
        self.mac = mac
        
    def set_dev_name(self, name):
        self.dev_name = name
    
    def set_rssi(self, rssi):
        self.rssi = rssi
        self.calculate_distance(rssi)
    
    def set_pi_discovery(self, pi):
        self.pi_discovery = pi
        
    def set_scan_date(self, scan_date):
        self.scan_date = scan_date

    def set_distance(self, distance):
        self.distance = distance
    
    
        
    # get methods 
    def get_gps(self):
        return self.gps
    
    def get_mac(self):
        return self.mac
        
    def get_dev_name(self):
        return self.dev_name
    
    def get_rssi(self):
        return self.rssi
    
    def get_pi_discovery(self):
        return self.pi_discovery
        
    def get_scan_date(self):
        return self.scan_date

    def get_distance(self):
        return self.distance
        
    def create_json(self):
        data = {}
        
        data['MAC_ADDRESS'] = self.mac
        data['DEVICE_NAME'] = self.dev_name
        data['RSSI'] = self.rssi
        data['DEVICE_LATITUTDE'] = self.longitude
        data['DEVICE_LONGITUDE'] = self.latitude
        data['PI_DISCOVERED_BY'] = self.pi_discovery
        data['SCAN_DATE'] = self.scan_date
        data['GPS'] = self.gps
        data['DISTANCE'] = self.distance
        
        json_data = json.dumps(data)
        
        return json_data
        
        
    def calculate_distance(self, rssi):
        """
            dist_0 = initial distance = 0.8m
            rssi_0 = RSSI at data reference d0
            x_mean = zero-mean Gaussian distributed random 
                    variable with standard deviation (from reference text = 14.1)
            n = path loss exponent (from reference text = 2.6)
            d = 10^{\frac{r-rssi_0-x_mean}{10n}}dist_0
        """
        dist_0 = 1
        #Nov19-0348 to get rssi 
        rssi_0 = 57
        #x_mean = 3.846
        #n = 1.447
        x_mean = 0
        n = 2
        
        log_exponent = (abs(rssi) - rssi_0 - x_mean) / (10*n)
        log_d = round((pow(10,log_exponent) * dist_0),2)
        
        
        """
            f = frequency in MHz
            N = distance power loss coefficient (28 for residential, 30 for an office at 2.4GHz)
            Pfn = floor loss penetration factor (10 for an apartment, 5 for a house, 14 for an office at 2.4GHz)
            d = 10^{\frac{r-20\log _{10}\left(f\right)-p+28}{n}}
        
        f = 2400
        N = 28
        P = 10
        
        itu_v1 = 20 * math.log10(f) 
        itu_exponent = (abs(rssi) - itu_v1 - P + 28) / N
        itu_d = pow(10,itu_exponent)
        
        #print("ITU: " + str(itu_exponent))  
        #print("Log: " + str(log_exponent))  
        """
        
        self.set_distance(log_d) 

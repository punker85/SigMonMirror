import math
import decimal 

# Get inpt for the GPS and distances
Latitude = input("Input Device 0's Latitude: ")
Longitude = input("Input Device 0's Longitude: ")
Distance_0_to_1 = input("Enter Distance from Device 0 to Device 1 in inches: ")
Distance_0_to_2 = input("Enter Distance from Device 0 to Device 2 in inches: ")
Distance_1_to_2 = input("Enter Distance from Device 1 to Device 2 in inches: ")
Bearing = input("Enter the Bearing from Device 0 to Device 1 in degrees from N: ")

# Convert inches to meters
Distance_0_to_1 = Distance_0_to_1 * 0.0254
Distance_0_to_2 = Distance_0_to_2 * 0.0254
Distance_1_to_2 = Distance_1_to_2 * 0.0254

degrees_per_meter = .000001 / .10247
meters_per_degrees = .10247 / .000001

# calculate the distance from North for device 1
adj_distance_0_to_1 = decimal.Decimal(Distance_0_to_1) * decimal.Decimal(math.cos(decimal.Decimal(Bearing)))
opp_distance_0_to_1 = decimal.Decimal(Distance_0_to_1) * decimal.Decimal(math.sin(decimal.Decimal(Bearing)))

# calculate the angle to device 2
angle_of_device_2 = 90 - decimal.Decimal(Bearing) - decimal.Decimal(math.degrees(math.atan(decimal.Decimal(Distance_1_to_2)/decimal.Decimal(Distance_0_to_2))))

# calculate the distance from North for device 2
adj_distance_0_to_2 = decimal.Decimal(Distance_0_to_2) * decimal.Decimal(math.cos(angle_of_device_2))
opp_distance_0_to_2 = decimal.Decimal(Distance_0_to_2) * decimal.Decimal(math.sin(angle_of_device_2))

# Calculate GPS based on distance from device 0
gps_lat_of_device_1 = decimal.Decimal(Latitude) + (decimal.Decimal(adj_distance_0_to_1) * decimal.Decimal(degrees_per_meter))
gps_long_of_device_1 = decimal.Decimal(Longitude) + (decimal.Decimal(opp_distance_0_to_1) * decimal.Decimal(degrees_per_meter))
gps_lat_of_device_2 = decimal.Decimal(Latitude) + (decimal.Decimal(adj_distance_0_to_2) * decimal.Decimal(degrees_per_meter))
gps_long_of_device_2 = decimal.Decimal(Longitude) + (decimal.Decimal(opp_distance_0_to_2) * decimal.Decimal(degrees_per_meter))

# output the gps coorindates
print("Latitude, Longitude of Device 1: " + str(gps_lat_of_device_1) + ", " + str(gps_long_of_device_1))
print("Latitude, Longitude of Device 2: " + str(gps_lat_of_device_2) + ", " + str(gps_long_of_device_2))

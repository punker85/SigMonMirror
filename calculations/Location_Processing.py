#Grab the values from the database based on time
#
import pymysql
import sys
import decimal
import math
import datetime

class controller:
    gps_lat = 0
    gps_long = 0
    x_coord = 0
    Y_coord = 0
    experiment_number = 0
    device_list = list()
    distance_to_0 = 0;
    distance_to_1 = 0;
    distance_to_2 = 0;
    start_time = ''

class single_device:
    mac_address = ""
    distance = 0
    interval_time = ""
    experiment_num = 0

class combined_device:
    mac_address = ""
    gps_lat = 0
    gps_long = 0
    x_coord = 0
    y_coord = 0
    distance_from_controller_0 = 0
    distance_from_controller_1 = 0
    distance_from_controller_2 = 0
    compute_flag = 0

def get_devices_from_db(controller_0, controller_1, controller_2):
    cred_lines = []                             # Declare an empty list named mylines.
    with open ('DB_Credentials.txt', 'rt') as cred_file: # Open lorem.txt for reading text data.
        for line in cred_file:                # For each line, stored as myline,
            cred_lines.append(line)           # add its contents to mylines.


    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit (1)

    cursor = db.cursor()
    sql = "SELECT DISTINCT mac FROM device WHERE (experiment = {0} OR experiment = {1} OR experiment = {2})".format(controller_0.experiment_number, controller_1.experiment_number, controller_2.experiment_number)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = list(cursor.fetchall())
       print(results)
    except:
       print("Error: unable to fetch data")

    # disconnect from server
    db.close()

    return results

def get_selected_device_from_db(temp_controller, mac_address):
    cred_lines = []
    results = []
    with open ('DB_Credentials.txt', 'rt') as cred_file: # Open lorem.txt for reading text data.
        for line in cred_file:                # For each line, stored as myline,
            cred_lines.append(line)           # add its contents to mylines.


    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit (1)

    cursor = db.cursor()
    sql = "SELECT * FROM dist WHERE (experiment = {0} AND mac = '{1}') ORDER BY start_time;".format(temp_controller, mac_address)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = list(cursor.fetchall())
    except:
       print("Error: unable to fetch selected devices")

    # disconnect from server
    db.close()

    return results

def get_controller_from_db(controller_0, controller_1, controller_2):
    cred_lines = []                             # Declare an empty list named mylines.
    with open ('DB_Credentials.txt', 'rt') as cred_file: # Open lorem.txt for reading text data.
        for line in cred_file:                # For each line, stored as myline,
            cred_lines.append(line)           # add its contents to mylines.


    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit (1)

    cursor = db.cursor()
    sql = "SELECT lat, lng, time_start FROM experiment WHERE id = {0}".format(controller_0.experiment_number)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = list(cursor.fetchall())
       for row in results:
           controller_0.gps_lat = row[0]
           controller_0.gps_long = row[1]
           controller_0.start_time = row[2]

    except:
       print("Error: unable to fetch data")

    sql = "SELECT lat, lng FROM experiment WHERE id = {0}".format(controller_1.experiment_number)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = list(cursor.fetchall())
       for row in results:
           controller_1.gps_lat = row[0]
           controller_1.gps_long = row[1]

    except:
       print("Error: unable to fetch data")

    sql = "SELECT lat, lng FROM experiment WHERE id = {0}".format(controller_2.experiment_number)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = cursor.fetchall()
       for row in results:
           controller_2.gps_lat = row[0]
           controller_2.gps_long = row[1]

    except:
       print("Error: unable to fetch data")

    print("controller 0 Lat, Long: {0}, {1}".format(controller_0.gps_lat, controller_0.gps_long))
    print("controller 1 Lat, Long: {0}, {1}".format(controller_1.gps_lat, controller_1.gps_long))
    print("controller 2 Lat, Long: {0}, {1}".format(controller_2.gps_lat, controller_2.gps_long))
    # disconnect from server
    db.close()

    return results

def insert_device_into_db(current_device, controller_0, controller_1, controller_2):
    cred_lines = []                             # Declare an empty list named mylines.
    with open ('DB_Credentials.txt', 'rt') as cred_file: # Open lorem.txt for reading text data.
        for line in cred_file:                # For each line, stored as myline,
            cred_lines.append(line)           # add its contents to mylines.


    try:
        db = pymysql.connect(host=cred_lines[0][:-1],
                             port=int(cred_lines[1][:-1]),
                             user=cred_lines[2][:-1],
                             password=cred_lines[3][:-1],
                             db=cred_lines[4][:-1])
    except pymysql.Error as e:
        print("Error {0}: {1}".format(e.args[0], e.args[1]))
        sys.exit (1)

    cursor = db.cursor()
    sql = "SELECT * FROM triplet WHERE (exp1 = {0} OR exp1 = {1} OR exp1 = {2})".format(controller_0.experiment_number, controller_1.experiment_number, controller_2.experiment_number)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       triple = list(cursor.fetchall())

    except:
       print("Error: unable to fetch data")

    sql = "INSERT INTO trilat(triple, mac, time, lat, lng) VALUES ({0},'{1}','{2}',{3},{4})".format(triple[0][0], current_device.mac_address, controller_0.start_time, current_device.gps_lat, current_device.gps_long)
    print(sql)
    # id, MAC, distance, interval start time, pi number, experiment number

    try:
        # Execute the SQL command
       cursor.execute(sql)
       # Fetch all the rows in a list of lists.
       results = list(cursor.fetchall())
    except:
       print("Error: unable to insert into database")

    # disconnect from server
    db.close()


def Compute_Location_of_Device(controller_0, controller_1, controller_2, current_device):
    decreasing_counter = 0
    increasing_counter = 0
    increasing_flag = 0
    decreasing_flag = 0
    offset = 2
    while(1):
        number_of_intersections = 0;
        # find how many intersections there are if 6 then see if they all meet at one point, otherwise increase the circle size
        # if true then the two circle intersect
        if(round(controller_0.distance_to_1,1) + offset < round(current_device.distance_from_controller_0 + current_device.distance_from_controller_1,1) and round(controller_0.distance_to_1,1) > round(abs(current_device.distance_from_controller_0 - current_device.distance_from_controller_1),1)):
            number_of_intersections += 2
        if(round(controller_0.distance_to_2,1) + offset < round(current_device.distance_from_controller_0 + current_device.distance_from_controller_2,1) and round(controller_0.distance_to_2,1) > round(abs(current_device.distance_from_controller_0 - current_device.distance_from_controller_2),1)):
            number_of_intersections += 2
        if(round(controller_1.distance_to_2,1) + offset < round(current_device.distance_from_controller_1 + current_device.distance_from_controller_2,1) and round(controller_1.distance_to_2,1) > round(abs(current_device.distance_from_controller_1 - current_device.distance_from_controller_2),1)):
            number_of_intersections += 2

        	#number_of_intersections = 6
        # check if the circles intersect at mulitple ponits (decrease circle size), one point(compute location), or no points (increase circle size)
        if(number_of_intersections == 6):

            x_vector_0_to_1 = decimal.Decimal(controller_1.x_coord - controller_0.x_coord) / decimal.Decimal(controller_0.distance_to_1)
            y_vector_0_to_1 = decimal.Decimal(controller_1.y_coord - controller_0.y_coord) / decimal.Decimal(controller_0.distance_to_1)
            x_vector_0_to_2 = decimal.Decimal(controller_2.x_coord - controller_0.x_coord) / decimal.Decimal(controller_0.distance_to_2)
            y_vector_0_to_2 = decimal.Decimal(controller_2.y_coord - controller_0.y_coord) / decimal.Decimal(controller_0.distance_to_2)
            x_vector_1_to_2 = decimal.Decimal(controller_2.x_coord - controller_1.x_coord) / decimal.Decimal(controller_1.distance_to_2)
            y_vector_1_to_2 = decimal.Decimal(controller_2.y_coord - controller_1.y_coord) / decimal.Decimal(controller_1.distance_to_2)

            intersection_0_to_1_x = decimal.Decimal(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(current_device.distance_from_controller_1 ** 2) + decimal.Decimal(controller_0.distance_to_1 ** 2))/ decimal.Decimal(2 * controller_0.distance_to_1)
            intersection_0_to_1_y = math.sqrt(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(intersection_0_to_1_x ** 2))

            intersection_0_to_2_x = decimal.Decimal(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(current_device.distance_from_controller_2 ** 2) + decimal.Decimal(controller_0.distance_to_2 ** 2))/ decimal.Decimal(2 * controller_0.distance_to_2)
            intersection_0_to_2_y = math.sqrt(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(intersection_0_to_2_x ** 2))

            intersection_1_to_2_x = decimal.Decimal(decimal.Decimal(current_device.distance_from_controller_1 ** 2) - decimal.Decimal(current_device.distance_from_controller_2 ** 2) + decimal.Decimal(controller_1.distance_to_2 ** 2))/ decimal.Decimal(2 * controller_1.distance_to_2)
            intersection_1_to_2_y = math.sqrt(decimal.Decimal(current_device.distance_from_controller_1 ** 2) - decimal.Decimal(intersection_1_to_2_x ** 2))

            p1_0_to_1_x = round(decimal.Decimal(controller_0.x_coord + decimal.Decimal(intersection_0_to_1_x) * decimal.Decimal(x_vector_0_to_1) - decimal.Decimal(intersection_0_to_1_y) * decimal.Decimal(y_vector_0_to_1)),1)
            p1_0_to_1_y = round(decimal.Decimal(controller_0.y_coord + decimal.Decimal(intersection_0_to_1_x) * decimal.Decimal(y_vector_0_to_1) + decimal.Decimal(intersection_0_to_1_y) * decimal.Decimal(x_vector_0_to_1)),1)
            p2_0_to_1_x = round(decimal.Decimal(controller_0.x_coord + decimal.Decimal(intersection_0_to_1_x) * decimal.Decimal(x_vector_0_to_1) + decimal.Decimal(intersection_0_to_1_y) * decimal.Decimal(y_vector_0_to_1)),1)
            p2_0_to_1_y = round(decimal.Decimal(controller_0.y_coord + decimal.Decimal(intersection_0_to_1_x) * decimal.Decimal(y_vector_0_to_1) - decimal.Decimal(intersection_0_to_1_y) * decimal.Decimal(x_vector_0_to_1)),1)
            p1_0_to_2_x = round(decimal.Decimal(controller_0.x_coord + decimal.Decimal(intersection_0_to_2_x) * decimal.Decimal(x_vector_0_to_2) - decimal.Decimal(intersection_0_to_2_y) * decimal.Decimal(y_vector_0_to_2)),1)
            p1_0_to_2_y = round(decimal.Decimal(controller_0.y_coord + decimal.Decimal(intersection_0_to_2_x) * decimal.Decimal(y_vector_0_to_2) + decimal.Decimal(intersection_0_to_2_y) * decimal.Decimal(x_vector_0_to_2)),1)
            p2_0_to_2_x = round(decimal.Decimal(controller_0.x_coord + decimal.Decimal(intersection_0_to_2_x) * decimal.Decimal(x_vector_0_to_2) + decimal.Decimal(intersection_0_to_2_y) * decimal.Decimal(y_vector_0_to_2)),1)
            p2_0_to_2_y = round(decimal.Decimal(controller_0.y_coord + decimal.Decimal(intersection_0_to_2_x) * decimal.Decimal(y_vector_0_to_2) - decimal.Decimal(intersection_0_to_2_y) * decimal.Decimal(x_vector_0_to_2)),1)
            p1_1_to_2_x = round(decimal.Decimal(controller_1.x_coord + decimal.Decimal(intersection_1_to_2_x) * decimal.Decimal(x_vector_1_to_2) - decimal.Decimal(intersection_1_to_2_y) * decimal.Decimal(y_vector_1_to_2)),1)
            p1_1_to_2_y = round(decimal.Decimal(controller_1.y_coord + decimal.Decimal(intersection_1_to_2_x) * decimal.Decimal(y_vector_1_to_2) + decimal.Decimal(intersection_1_to_2_y) * decimal.Decimal(x_vector_1_to_2)),1)
            p2_1_to_2_x = round(decimal.Decimal(controller_1.x_coord + decimal.Decimal(intersection_1_to_2_x) * decimal.Decimal(x_vector_1_to_2) + decimal.Decimal(intersection_1_to_2_y) * decimal.Decimal(y_vector_1_to_2)),1)
            p2_1_to_2_y = round(decimal.Decimal(controller_1.y_coord + decimal.Decimal(intersection_1_to_2_x) * decimal.Decimal(y_vector_1_to_2) - decimal.Decimal(intersection_1_to_2_y) * decimal.Decimal(x_vector_1_to_2)),1)

            if(p1_0_to_1_x == p1_0_to_2_x and p1_0_to_1_y == p1_0_to_2_y and p1_0_to_1_x == p1_1_to_2_x and p1_0_to_1_y == p1_1_to_2_y):
                current_device.x_coord = p1_0_to_1_x
                current_device.y_coord = p1_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p1_0_to_1_x == p1_0_to_2_x and p1_0_to_1_y == p1_0_to_2_y and p1_0_to_1_x == p2_1_to_2_x and p1_0_to_1_y == p2_1_to_2_y):
                current_device.x_coord = p1_0_to_1_x
                current_device.y_coord = p1_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p1_0_to_1_x == p2_0_to_2_x and p1_0_to_1_y == p2_0_to_2_y and p1_0_to_1_x == p1_1_to_2_x and p1_0_to_1_y == p1_1_to_2_y):
                current_device.x_coord = p1_0_to_1_x
                current_device.y_coord = p1_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p1_0_to_1_x == p2_0_to_2_x and p1_0_to_1_y == p2_0_to_2_y and p1_0_to_1_x == p2_1_to_2_x and p1_0_to_1_y == p2_1_to_2_y):
                current_device.x_coord = p1_0_to_1_x
                current_device.y_coord = p1_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p2_0_to_1_x == p1_0_to_2_x and p2_0_to_1_y == p1_0_to_2_y and p2_0_to_1_x == p1_1_to_2_x and p2_0_to_1_y == p1_1_to_2_y):
                current_device.x_coord = p2_0_to_1_x
                current_device.y_coord = p2_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p2_0_to_1_x == p1_0_to_2_x and p2_0_to_1_y == p1_0_to_2_y and p2_0_to_1_x == p2_1_to_2_x and p2_0_to_1_y == p2_1_to_2_y):
                current_device.x_coord = p2_0_to_1_x
                current_device.y_coord = p2_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p2_0_to_1_x == p2_0_to_2_x and p2_0_to_1_y == p2_0_to_2_y and p2_0_to_1_x == p1_1_to_2_x and p2_0_to_1_y == p1_1_to_2_y):
                current_device.x_coord = p2_0_to_1_x
                current_device.y_coord = p2_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(p2_0_to_1_x == p2_0_to_2_x and p2_0_to_1_y == p2_0_to_2_y and p2_0_to_1_x == p2_1_to_2_x and p2_0_to_1_y == p2_1_to_2_y):
                current_device.x_coord = p2_0_to_1_x
                current_device.y_coord = p2_0_to_1_y
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break
            elif(increasing_counter > 1500 and decreasing_counter > 1500):
                current_device.compute_flag = 1
                compute_gps_of_device(controller_0, controller_1, controller_2, current_device)
                break

            else:
                decreasing_counter += 1
                if(decreasing_flag == 0):
                    decreasing_flag = 1
                    increasing_flag = 0
                elif(decreasing_counter < 1000):
                    current_device.distance_from_controller_0 -= decimal.Decimal(0.005)
                    current_device.distance_from_controller_1 -= decimal.Decimal(0.005)
                    current_device.distance_from_controller_2 -= decimal.Decimal(0.005)
                elif(current_device.distance_from_controller_0 >= current_device.distance_from_controller_1 and current_device.distance_from_controller_0 >= current_device.distance_from_controller_2):
                    current_device.distance_from_controller_0 -= decimal.Decimal(0.005)
                elif(current_device.distance_from_controller_1 >= current_device.distance_from_controller_0 and current_device.distance_from_controller_1 >= current_device.distance_from_controller_2):
                    current_device.distance_from_controller_1 -= decimal.Decimal(0.005)
                elif(current_device.distance_from_controller_2 >= current_device.distance_from_controller_1 and current_device.distance_from_controller_2 >= current_device.distance_from_controller_0):
                    current_device.distance_from_controller_2 -= decimal.Decimal(0.005)
        else:
            increasing_counter += 1
            if(increasing_flag == 0):
                decreasing_flag = 0
                increasing_flag = 1
            elif(increasing_counter < 1000):
                current_device.distance_from_controller_0 += decimal.Decimal(0.005)
                current_device.distance_from_controller_1 += decimal.Decimal(0.005)
                current_device.distance_from_controller_2 += decimal.Decimal(0.005)
            elif(current_device.distance_from_controller_0 <= current_device.distance_from_controller_1 and current_device.distance_from_controller_0 <= current_device.distance_from_controller_2):
                offset = 0
                current_device.distance_from_controller_0 += decimal.Decimal(0.005)
            elif(current_device.distance_from_controller_1 <= current_device.distance_from_controller_0 and current_device.distance_from_controller_1 <= current_device.distance_from_controller_2):
                offset = 0
                current_device.distance_from_controller_1 += decimal.Decimal(0.005)
            elif(current_device.distance_from_controller_2 <= current_device.distance_from_controller_1 and current_device.distance_from_controller_2 <= current_device.distance_from_controller_0):
                offset = 0
                current_device.distance_from_controller_2 += decimal.Decimal(0.005)


def compute_gps_of_device(controller_0, controller_1, controller_2, current_device):
        if(current_device.compute_flag == 1):
            current_device.x_coord = (decimal.Decimal(decimal.Decimal(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(current_device.distance_from_controller_1 ** 2) + decimal.Decimal(controller_1.x_coord ** 2))) / decimal.Decimal(2 * controller_1.x_coord))
            current_device.y_coord = decimal.Decimal(decimal.Decimal(current_device.distance_from_controller_0 ** 2) - decimal.Decimal(current_device.distance_from_controller_2 ** 2) + decimal.Decimal(controller_2.x_coord ** 2) + decimal.Decimal(controller_2.y_coord ** 2) - decimal.Decimal(2 * controller_2.x_coord * current_device.x_coord)) / decimal.Decimal(2 * abs(controller_2.y_coord))
            current_device.compute_flag = 0
        print("Computed X coord: {0}".format(current_device.x_coord))
        print("Computed Y coord: {0}".format(current_device.y_coord))

        degrees_per_meter = decimal.Decimal(.000001 / .11132) # N/S doesn't matter all the sam
        current_device.gps_lat = decimal.Decimal(controller_0.gps_lat) + (decimal.Decimal(current_device.y_coord) * decimal.Decimal(degrees_per_meter))
        current_device.gps_long = decimal.Decimal(controller_0.gps_long) + (decimal.Decimal(current_device.x_coord) * decimal.Decimal(degrees_per_meter))
        print("Computed GPS Latitude: {0}".format(current_device.gps_lat))
        print("Computed GPS Longitude: {0}".format(current_device.gps_long))

def calculate_controller_location(controller_0, controller_1, controller_2):
    # create temp controller
    temp_controller = controller()
    # Lowest value Lat and Long = SW most device
    if((controller_1.gps_long <= controller_0.gps_long) & (controller_1.gps_long <= controller_2.gps_long)):
        temp_controller = controller_0
        controller_0 = controller_1
        controller_1 = temp_controller

       # highest GPS Lat should be the north most point
        if(controller_1.gps_lat < controller_2.gps_lat):
           temp_controller = controller_1
           controller_1 = controller_2
           controller_2 = temp_controller

    elif((controller_2.gps_long <= controller_0.gps_long) & (controller_2.gps_long <= controller_1.gps_long)):
        temp_controller = controller_0
        controller_0 = controller_2
        controller_2 = temp_controller

        # highest GPS Lat should be the north most point
        if(controller_1.gps_lat < controller_2.gps_lat):
           temp_controller = controller_1
           controller_1 = controller_2
           controller_2 = temp_controller

    else:
        # highest GPS Lat should be the north most point
       if(controller_1.gps_lat < controller_2.gps_lat):
           temp_controller = controller_1
           controller_1 = controller_2
           controller_2 = temp_controller

    print("controller 0 Lat, Long: {0}, {1}".format(controller_0.gps_lat, controller_0.gps_long))
    print("controller 1 Lat, Long: {0}, {1}".format(controller_1.gps_lat, controller_1.gps_long))
    print("controller 2 Lat, Long: {0}, {1}".format(controller_2.gps_lat, controller_2.gps_long))

def calculate_x_y_coordinates(controller_0, controller_1, controller_2):
    meters_per_degree = decimal.Decimal(.11132 / .000001) # N/S doesn't matter all the same

    # set controller 0 as the origin
    controller_0.x_coord = 0
    controller_0.y_coord = 0

    controller_1.x_coord = round((controller_1.gps_long - controller_0.gps_long) * meters_per_degree,5)
    controller_1.y_coord = round((controller_1.gps_lat - controller_0.gps_lat) * meters_per_degree,5)
    controller_1.distance_to_0 = round(math.sqrt(controller_1.x_coord **2 + controller_1.y_coord ** 2),5)
    controller_0.distance_to_1 = controller_1.distance_to_0
    print("controller 1 x,y: {0}, {1}".format(controller_1.x_coord, controller_1.y_coord))
    print("Distance to controller 0: {0}".format(controller_1.distance_to_0))

    controller_2.x_coord = round((controller_2.gps_long - controller_0.gps_long) * meters_per_degree,5)
    controller_2.y_coord = round((controller_2.gps_lat - controller_0.gps_lat) * meters_per_degree,5)
    controller_2.distance_to_0 = round(math.sqrt(controller_2.x_coord **2 + controller_2.y_coord ** 2),5)
    controller_0.distance_to_2 = controller_2.distance_to_0
    print("controller 2 x,y: {0}, {1}".format(controller_2.x_coord, controller_2.y_coord))
    print("Distance to controller 0: {0}".format(controller_2.distance_to_0))

    controller_1.distance_to_2 = round(math.sqrt(((controller_1.x_coord - controller_2.x_coord) ** 2) + ((controller_1.y_coord - controller_2.y_coord) ** 2)),5)
    controller_2.distance_to_1 = controller_1.distance_to_2
    print("Distance from controller 1 to 2: {0}".format(controller_2.distance_to_1))

def sort_devices(controller_0, controller_1, controller_2, current_device):
    if(current_device.experiment_number == controller_0.experiment_number):
        controller_0.device_list.append(current_device)
    elif(current_device.experiment_number == controller_1.experiment_number):
        controller_1.device_list.append(current_device)
    elif(current_device.experiment_number == controller_2.experiment_number):
        controller_2.device_list.append(current_device)



if __name__ == '__main__':
    # initialize 3 controllers
    controller_0 = controller()
    controller_1 = controller()
    controller_2 = controller()
    computed_devices = list([])

    file_output = open("gps_device_values.txt","w+")

    # Get experiment values for each controller
    controller_0.experiment_number = input("Enter experiment number for device 0: ")
    controller_1.experiment_number = input("Enter experiment number for device 1: ")
    controller_2.experiment_number = input("Enter experiment number for device 2: ")

    # Grab GPS locations for each Controller from the database
    list_of_controllers = get_controller_from_db(controller_0, controller_1, controller_2)
    file_output.write(str(controller_0.gps_lat) + "," + str(controller_0.gps_long) + "\n")
    file_output.write(str(controller_1.gps_lat) + "," + str(controller_1.gps_long) + "\n")
    file_output.write(str(controller_2.gps_lat) + "," + str(controller_2.gps_long) + "\n")
    # Determine SW most device and North most device
    #calculate_controller_location(controller_0, controller_1, controller_2)

    # Set X and Y coordinates for each controller
    calculate_x_y_coordinates(controller_0, controller_1, controller_2)

    # Grab all the devices mac addresses that were capture9d in the experiment
    list_of_devices = get_devices_from_db(controller_0, controller_1, controller_2)
    print(list_of_devices)
    current_device = combined_device()

    # Loop through list to sort each device to right controller, calculates one device at a time
    for i in list_of_devices:
        print(i)
        # Grab all the distances for the specified mac address and the experiment ordered by time
        controller_0.device_list = get_selected_device_from_db(controller_0.experiment_number, i[0])
        controller_1.device_list = get_selected_device_from_db(controller_1.experiment_number, i[0])
        controller_2.device_list = get_selected_device_from_db(controller_2.experiment_number, i[0])

        print(controller_0.device_list)
        print(controller_1.device_list)
        print(controller_2.device_list)

        #if((not controller_0.device_list) and (not controller_1.device_list) and (not controller_2.device_list)):
            #break

        # Set each controller start time
        if((controller_0.device_list) and (controller_1.device_list) and (controller_2.device_list)):
            controller_0.start_time = controller_0.device_list[0][3]
            controller_1.start_time = controller_1.device_list[0][3]
            controller_2.start_time = controller_2.device_list[0][3]

            # calculate GPS
            current_device.mac_address = controller_0.device_list[0][2] # verify that 1 is the mac address
            current_device.distance_from_controller_0 = controller_0.device_list[0][4]
            current_device.distance_from_controller_1 = controller_1.device_list[0][4]
            current_device.distance_from_controller_2 = controller_2.device_list[0][4]
            Compute_Location_of_Device(controller_0, controller_1, controller_2, current_device)

            computed_devices.append(current_device)
            file_output.write(str(current_device.gps_lat) + "," + str(current_device.gps_long) + "\n")

            # Add GPS of device to database
            insert_device_into_db(current_device, controller_0, controller_1, controller_2)

    #print(str(controller_0.gps_lat) + "," + str(controller_0.gps_long))
    #print(str(controller_1.gps_lat) + "," + str(controller_1.gps_long))
    #print(str(controller_2.gps_lat) + "," + str(controller_2.gps_long))

    #for devices in computed_devices:
    	#print(str(devices.gps_lat) + "," + str(devices.gps_long))
    file_output.close

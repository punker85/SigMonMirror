import sys
import time
from datetime import datetime
from datetime import timedelta
from collections import defaultdict
import controller
import device
import statistics


time_interval = 2
time_offset = 8
sample_interval = 6
def group_samples(samples):
    change_start = 0
    samples_dict = {}
    avg_samples = {}
    interval_values = {}
    
    start = samples[0]
    list_dev = start.split(' | ')
    start_time = list_dev[0]
    start_datetime = datetime.strptime(start_time, '%Y-%m-%d %H:%M:%S')
    
    count = 0
    for sample in samples:
        
        sample_list = sample.split(' | ')
        mac = sample_list[1]
        distance = sample_list[2]
        time = sample_list[0]
        if mac not in avg_samples:
            avg_samples[mac] = []
        avg_samples[mac].append(distance)

        time_datetime = datetime.strptime(time, '%Y-%m-%d %H:%M:%S')
        
        if change_start == 1:
            start_datetime = time_datetime
            change_start = 0
            
        td = time_datetime - start_datetime
        
        if td.total_seconds() >= time_interval*60+time_offset:
            count = count+1
            change_start = 1
            interval_values[start_datetime] = avg_samples
            avg_samples = {}
        
    interval_values[start_datetime] = avg_samples
            
    return interval_values
        

def kalman_filtering(samples_over_time):
    a = 0.75
    distances = []
    for sample in samples_over_time:

        macs = samples_over_time[sample]
        for mac in macs:
            dist_values = macs[mac]

            cumsum, moving_aves = [0], []
            
            for i, x in enumerate(dist_values, 1):
                cumsum.append(cumsum[i-1] + float(x))
                if i>=sample_interval+1:
                    moving_ave = (cumsum[i-1] - cumsum[i-sample_interval-1])/sample_interval
                    kalman_val = a*float(x)+(1-a)*moving_ave
                    moving_aves.append(round(kalman_val,5))
            
            if moving_aves:
                distance = round(statistics.median(moving_aves),2)
                ref_time = sample
                experiment = 0
                filtered_sample = [mac, distance, str(ref_time)]
                distances.append(filtered_sample)
                   
    return distances        
        
    

    



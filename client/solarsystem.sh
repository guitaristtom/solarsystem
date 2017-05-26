#!/usr/bin/env bash

#--------------------------------
#------------Settings------------
#--------------------------------
#----Change settings below to----
#--------match your needs--------
#--------------------------------
declare -A settings

# Servers's IP
settings[serverIP]='192.168.1.1'
settings[uploadLocation]='/solarsystem/web/update.php'
settings[uuid]='UUID-GOES-HERE'
settings[key]='KEY-UUID-GOES-HERE'
settings[startAtZero]=true

# Double Check that the server is available?
settings[doubleCheckServer]=true

# Temperature
## Do temperature?
settings[doTemp]=true

## Array/list of items to get the temperature for.
declare -A temperatureList
## For each item, create a new line, following the example below
## ex: temperatureList[nouveau-pci-0200]="GPU"
temperatureList[coretemp-isa-0000]="CPU 1"

#--------------------------------
#----------End Settings----------
#--------------------------------

# Server Check
if [ ${settings[doubleCheckServer]} = true ]; then
    ping -c 1 ${settings[serverIP]} > /dev/null
    pingResult=$?
    if [[ ! $pingResult -eq 0 ]] ; then
        echo "Server is either not reachable or is offline"
        exit 1
    fi
fi

# Temperature
if [ ${settings[doTemp]} = true ]; then
    for name in "${!temperatureList[@]}"; do
        # Grabs the three different standard outputs from lm-sensors
        tempInput=($(sensors -u "${name}" | grep 'temp[0-9]_input:' | sed 's/\s*temp[0-9]_input:\s//g'))
        tempMax=($(sensors -u "${name}" | grep 'temp[0-9]_max:' | sed 's/\s*temp[0-9]_max:\s//g'))
        tempCrit=($(sensors -u "${name}" | grep 'temp[0-9]_crit:' | sed 's/\s*temp[0-9]_crit:\s//g'))

        # Grabs what adapter the item is hooked up with. **WIP** cuts off after first word
        adapter=($(sensors -u $name | grep 'Adapter:' | sed 's/\s*Adapter:\s//g'))

        for (( i=0; i<${#tempInput[@]}; i++ )); do
            # If set to not start at zero, then it adds one
            tempNumber=$((i))
            if [ ${settings[startAtZero]} = false ]; then
                tempNumber=$((tempNumber+1))
            fi

            cURLParameters="update_type=temperature&"
            cURLParameters="${cURLParameters}uuid=${settings[uuid]}&"
            cURLParameters="${cURLParameters}key=${settings[key]}&"
            cURLParameters="${cURLParameters}chip_name=${name}&"
            cURLParameters="${cURLParameters}temp_number=${tempNumber}&"
            cURLParameters="${cURLParameters}display_name=${temperatureList[$name]}&"
            cURLParameters="${cURLParameters}adapter=${adapter}&"
            cURLParameters="${cURLParameters}temp_input=${tempInput[$i]}&"
            cURLParameters="${cURLParameters}temp_max=${tempMax[$i]}&"
            cURLParameters="${cURLParameters}temp_crit=${tempCrit[$i]}"

            curl --data "${cURLParameters}" ${settings[serverIP]}${settings[uploadLocation]}
        done
    done
fi
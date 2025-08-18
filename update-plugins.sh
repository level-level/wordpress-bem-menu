#!/bin/bash

version=v1.1.0
echo "Updating dependencies. Script version: ${version}"

composer outdated -m | while read line;
do
        read -a arr <<< $line
        plugin=${arr[0]}

        # Check if the line is actually for a package, and not an error.
        if ! [[ "$plugin" == *"/"* ]]; then
                continue;
        fi

        # Setup versions
        current=${arr[1]}
        latest=${arr[3]}

        # If no latest version is available, skip it
        if [[ -z $latest ]]; then
                continue
        fi

        echo "composer update ${plugin} -W"
        composer update "$plugin" &&
        git add composer.json &&
        git add composer.lock &&
        git commit -m "'update ${plugin} '";
done;

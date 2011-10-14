#1/bin/bash

chown www-data:www-data * -R
chmod u+rw-x+X,g+r-wx+X,o+r-wx+X * -R

chmod +x rights.sh
chmod 777 var

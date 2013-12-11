#!/bin/bash

# $Id: drop_reload.sh 3158 2010-09-02 02:49:00Z gullevek $

rm error;
rm output;
bin/drop_data.sh;
bin/import_data.sh;

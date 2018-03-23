#!/bin/bash

OIFS=${IFS};
base_dir="/home/clemens/html/developers/clemens/core_data/php_libraries/trunk/";
class_file="CoreLibs/Output/Form/Generate.inc";
tmp_file=${base_dir}"4dev/tmp/tmp.comp."$(echo "${class_file}" | tr \/. _);
rpl_file=${base_dir}"4dev/tmp/tmp.rpl."$(echo "${class_file}" | tr \/. _);
rm -f "${tmp_file}";
rm -f "${rpl_file}";
if [ ! -f ${class_file} ];
then
    echo "Cannot find ${class_file} in current folder: $(pwd)";
    exit;
fi;
cat "${class_file}" | grep "WAS   :" -B 1 | while read line;
do
    # if method grep for function call
    found=$(echo "${line}" | sed -e 's/^[ \t]*//' | grep "METHOD:");
    if [ -n "${found}" ];
    then
        method=$(echo "${line}" | cut -d " " -f 3);
        echo "1 MET: ${method}";
        # is method
        if [ -n "${method}" ];
        then
            # the full new call
            new_function_call_full=$(grep "function ${method}(" "${class_file}" | grep "function" | sed -e 's/^[ \t]*//');
            # just the method name
            new_function_call=$(echo "${new_function_call_full}" | sed -e 's/public //' | sed -e 's/private //' | sed -e 's/static //' | sed -e 's/function //' | cut -d "(" -f 1);
            # check if func call is more than just alphanumeric (we don't need to redeclare those, functions are case insenstivie)
            #
            # only params (remove all = ... stuff)
            new_function_call_params=$(echo "${new_function_call_full}" | cut -d "(" -f 2- | sed -e 's/)//');
            old_function_call_params='';
            IFS=',';
            for el in ${new_function_call_params};
            do
                if [ -n "${old_function_call_params}" ];
                then
                    old_function_call_params=${old_function_call_params}", ";
                fi;
                old_function_call_params=${old_function_call_params}$(echo "${el}" | cut -d "=" -f 1 | tr -d ' ');
            done;
            # cut -d "," "${new_function_call_params}" | while
        fi;
    fi;
    # if this is a WAS
    was=$(echo "${line}" | sed -e 's/^[ \t]*//' | grep "WAS   :" | tr -s " ");
    if [ -n "${was}" ];
    then
        old_function_call=$(echo "${was}" | cut -d " " -f 4)
        echo "2 OLD: ${old_function_call} => ${new_function_call} [${new_function_call_full}]";
        # for return write:
        # rpl new -> old { new }
        rpl=$(echo "${new_function_call_full}" | sed -e "s/${new_function_call}/${old_function_call}/");
        new_call="${rpl}\n";
        new_call=${new_call}"{\n";
        new_call=${new_call}"\terror_log('DEPRECATED CALL: '.__METHOD__.', '.__FILE__.':'.__LINE__.', '.debug_backtrace()[0]['file'].':'.debug_backtrace()[0]['line']);\n";
        new_call=${new_call}"\treturn \$this->${new_function_call}(${old_function_call_params});\n";
        new_call=${new_call}"}\n";
        echo -e "${new_call}" >> "${tmp_file}";
        echo "3A RPL CALL: ${rpl}";
        echo "3B RPL CALL: return \$this->${new_function_call}(${old_function_call_params});";
        echo "4  SWT RPL : rpl '\$this->${old_function_call}' '\$this->${new_function_call}'";
        # write the replace calls for old $this->old_call to $this->new_call
        echo "rpl '\$this->${old_function_call}' '\$this->${new_function_call}' ##TARGET_FILE##" >> "${rpl_file}";
        echo "----";
    fi;
done;
IFS=${OIFS};

# __END__

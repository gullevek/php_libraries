#!/bin/bash

OIFS=${IFS};
class_file="Class.Basic.inc";
tmp_file="../../4dev/tmp/tmp.comp";
rm -f "${tmp_file}";
cat "${class_file}" | grep "WAS" -B 1 | while read line;
do
    # if method grep for function call
    found=$(echo "${line}" | sed -e 's/^[ \t]*//' | grep "METHOD");
    if [ -n "${found}" ];
    then
        method=$(echo "${line}" | cut -d " " -f 3);
        echo "1 MET: ${method}";
        # is method
        if [ -n "${method}" ];
        then
            # the full new call
            new_function_call_full=$(grep "${method}(" Class.Basic.inc | grep "function" | sed -e 's/^[ \t]*//');
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
    was=$(echo "${line}" | sed -e 's/^[ \t]*//' | grep "WAS" | tr -s " ");
    if [ -n "${was}" ];
    then
        old_function_call=$(echo "${was}" | cut -d " " -f 3)
        echo "2 OLD: ${old_function_call} => ${new_function_call} [${new_function_call_full}]";
        # for return write:
        # rpl new -> old { new }
        rpl=$(echo "${new_function_call_full}" | sed -e "s/${new_function_call}/${old_function_call}/");
        new_call="${rpl}\n";
        new_call=${new_call}"{\n";
        new_call=${new_call}"\t\$this->debug('DEPRECATED CALL', __FUNCTION);\n";
        new_call=${new_call}"\t\$this->${new_function_call}(${old_function_call_params});\n";
        new_call=${new_call}"}\n";
        echo -e "${new_call}" >> "${tmp_file}";
        echo "3A RPL CALL: ${rpl}";
        echo "3B RPL CALL: {";
        echo "3B RPL CALL: \$this->debug('DEPRECATED CALL', __FUNCTION);";
        echo "3B RPL CALL: ${new_function_call}(${old_function_call_params});";
        echo "3B RPL CALL: }";
        echo "----";
    fi;
done;
IFS=${OIFS};
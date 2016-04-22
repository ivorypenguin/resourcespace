<?php
function csv_user_import_process($csv_file, $user_group_id, &$messages, $processcsv = false)
    {
    global $defaultlanguage;

    $mandatory_columns = array('username', 'password', 'email');
    $optional_columns  = array('fullname', 'account_expires', 'comments', 'ip_restrict', 'lang');
    $possible_columns  = array_merge($mandatory_columns, $optional_columns);

    $default_columns_values = array(
        'lang' => $defaultlanguage
    );

    $file = fopen($csv_file, 'r');

    // Manipulate headers
    $headers = fgetcsv($file);
    if(!$headers)
        {
        array_push($messages, 'No header found');
        fclose($file);

        return false;
        }

    for($i = 0; $i < count($headers); $i++)
        {
        $headers[$i] = mb_strtolower($headers[$i]);
        }



    // Check header columns
    $header_check_valid = false;
    $mandatory_columns_not_found = array_diff($headers, $optional_columns);
    $mandatory_columns_not_found = array_diff($mandatory_columns_not_found, $mandatory_columns);
    foreach($mandatory_columns_not_found as $column_header)
        {
        array_push($messages, 'Error: Could not find mandatory column "' . $column_header . '"');
        $header_check_valid = true;
        }

    $unknown_columns = array_diff($headers, $possible_columns);
    foreach($unknown_columns as $column_header)
        {
        array_push($messages, 'Info: ResourceSpace has no use (ie. unknown) for column "' . $column_header . '" and as such it will not be taken into account');
        }

    // No point to continue since headers are not right
    if($header_check_valid)
        {
        fclose($file);

        return false;
        }



    $line_count      = 0;
    $error_count     = 0;
    $max_error_count = 100;

    array_push($messages, '### Processing ' . count($headers) . ' columns ###');

    while( ( false !== ($line = fgetcsv($file)) ) && $error_count < $max_error_count)
        {
        $line_count++;

        // Check that the current row has the correct number of columns
        if(!$processcsv && count($line) !== count($headers))
            {
            array_push($messages, 'Error: Incorrect number of columns( ' . count($line) . ') found on line ' . $line_count . '. It should be ' . count($headers));
            $error_count++;

            continue;
            }

        $sql_update_col_val_pair = "`usergroup` = '" . escape_check($user_group_id) . "'";
        $cell_count = -1;
        foreach($headers as $header)
            {
            $cell_count++;
            $cell_value = trim($line[$cell_count]);

            // Make sure mandatory fields have a value
            if(in_array($header, $mandatory_columns) && '' === $cell_value)
                {
                array_push($messages, 'Error: Mandatory column "' . $header . '" cannot be empty on line ' . $line_count);
                $error_count++;

                continue;
                }

            if('username' === $header || 'email' === $header)
                {
                $check = sql_value("SELECT count(*) AS value FROM user WHERE `{$header}` = '{$cell_value}'", 0);
                if(0 < $check)
                    {
                    array_push($messages, ucfirst($header). ' "' . $cell_value . '" exists already in ResourceSpace');
                    $error_count++;

                    continue;
                    }
                }

            // Create new user if we can process it and don't have any errors
            if($processcsv && 0 === $error_count && 'username' === $header)
                {
                $new_user_id = new_user($cell_value);
                if(isset($new_user_id))
                    {
                    array_push($messages, 'Info: Created new user "' . $cell_value . '" with ID "' . $new_user_id . '"');
                    }

                continue;
                }

            $sql_update_col_val_pair .= ", `" . escape_check($header) . "` = ";
            if('' === $cell_value && array_key_exists($header, $default_columns_values))
                {
                $sql_update_col_val_pair .= "'" . escape_check($default_columns_values[$header]) . "'";
                }
            else if('' === $cell_value)
                {
                $sql_update_col_val_pair .= 'NULL';
                }
            else
                {
                $sql_update_col_val_pair .= "'" . escape_check($cell_value) . "'";
                }
            }

        if($processcsv && 0 === $error_count && isset($new_user_id))
            {
            // Update record
            $sql_query = "UPDATE `user` SET {$sql_update_col_val_pair} WHERE `ref` = '{$new_user_id}'";
            sql_query($sql_query);
            }

        } /* end of reading each line found */

    fclose($file);

    if(!$processcsv && 1 === $line_count)
        {
        array_push($messages, 'Error: No lines of data found in the uploaded file');

        return false;
        }

    // Consider removing if not much is going on through each line
    if(!$processcsv && 0 < $error_count)
        {
        array_push($messages, 'Warning: Script has found ' . $error_count . ' error(s)!');

        return false;
        }

    if(!$processcsv)
        {
        array_push($messages, 'Info: data successfully validated!');
        }
    else
        {
        array_push($messages, 'Info: data successfully processed!');
        }

    return true;
    }
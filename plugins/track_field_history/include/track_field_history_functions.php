<?php
if (!function_exists('track_field_history_get_field_log')) {
    
    function track_field_history_get_field_log($resource_id, $field_id) {
    
        $query = sprintf('
                   SELECT resource_log.date AS date,
                          IFNULL(user.fullname, user.username) AS user,
                          resource_log.diff
                     FROM resource_log
                LEFT JOIN user ON user.ref = resource_log.user
                    WHERE type = "e"
                      AND resource = %d
                      AND resource_type_field = %d
                 ORDER BY resource_log.date DESC;
            ',
            $resource_id,
            $field_id
        );
        return sql_query($query);

    }
}


<?php
    function getUserName(){
        global $current_user;
        $current_user = wp_get_current_user();
        return $current_user->display_name;
        //return $current_user->user_firstname;
    }

    function currentPage(){
        global $current_page;
        $current_page = get_site_url().'/'.basename(get_permalink());
        return $current_page;
    }

    function connection(){
        $link = mysqli_connect(DB_HOST,DB_USER,DB_PASSWORD,DB_NAME);
        if ($link->connect_error) {
            return "Connection failed: " . $link->connect_error;
        }
        return $link;
    }

    function goBack(){
        echo "<script type='text/javascript'>document.write(\"<a href='javascript:history.back()' class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>\");</script>
        <noscript>
            <a href='".$_SERVER['HTTP_REFERER']."‘ class='backLink' title='Voltar atr&aacute;s'>Voltar atr&aacute;s</a>
        </noscript>";
    }

    function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }

    function get_enum_values($connection, $table, $column )
    {
        $query = " SHOW COLUMNS FROM `$table` LIKE '$column' ";
        $result = mysqli_query($connection, $query );
        $row = mysqli_fetch_array($result , MYSQLI_NUM );
        #extract the values
        #the values are enclosed in single quotes
        #and separated by commas
        $regex = "/'(.*?)'/";
        preg_match_all( $regex , $row[1], $enum_array );
        $enum_fields = $enum_array[1];
        return( $enum_fields );
    }


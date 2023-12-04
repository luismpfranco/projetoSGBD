<?php
require_once("custom/php/common.php");

function createTable(){
    $query = "SELECT * FROM child ORDER BY name ASC";
    $result = mysqli_query(connection(),$query);
    $num_rows = mysqli_num_rows($result);

    echo '<table cellspacing="2" cellpadding="2" border="1" width="100%">';
        echo '<tbody>';
            echo '<tr>';
                echo '<td><b>Nome</b></td>';
                echo '<td><b>Data de nascimento</b></td>';
                echo '<td><b>Enc. de educação</b></td>';
                echo '<td><b>Telefone do Enc.</b></td>';
                echo '<td><b>E-mail</b></td>';
                echo '<td><b>Registos</b></td>';
            echo '</tr>';

    if($num_rows == 0){
        echo '<td colspan="6" style="text-align:center">'."Não há crianças".'</td>'; //CRIAR UM CSS
        mysqli_free_result($result);
    }else {
        while ($row = $result->fetch_array()){
            echo '<tr>';
                echo '<td>'.$row['name'].'</td>';
                echo '<td>'.$row['birth_date'].'</td>';
                echo '<td>'.$row['tutor_name'].'</td>';
                echo '<td>'.$row['tutor_phone'].'</td>';
                echo '<td>'.$row['tutor_email'].'</td>';

                $query_items = 'SELECT DISTINCT(item.name) as item_name, item.id as item_id
                                    FROM item, subitem, value
                                    WHERE subitem.item_id = item.id AND 
                                          value.subitem_id = subitem.id AND 
                                          value.child_id = "'.$row["id"].'"';
                $result_items = mysqli_query(connection(), $query_items);

                echo '<td>';
                while($row_items = $result_items->fetch_assoc()){
                    $string_register =  strtoupper($row_items["item_name"]) . ':' . '<br>';
                    $query_values = 'SELECT subitem.name as subitem_name, value.value, value.date 
                                     FROM subitem, value
                                     WHERE value.subitem_id = subitem.id AND
	                                       value.value != "" AND
                                           value.child_id = "'.$row["id"].'" AND
                                           subitem.item_id = "'.$row_items["item_id"].'"
                                           GROUP BY value.value
                                           ORDER BY subitem.name ASC';
                    $result_values = mysqli_query(connection(), $query_values);

                    //Preenchemos os string de cada criança
                    while ($row_value = $result_values->fetch_assoc()){
                        $string_register .= '[editar][apagar] - ' . '<strong>' . $row_value["date"] . '</strong>' . ' ('.getUserName().') - ' . '<strong>' . $row_value["subitem_name"] . '</strong>' . ' ( ' . $row_value["value"] . ' ) ' . '<br>';
                    }
                    echo $string_register;
                }
                echo '</td>';

            echo '</tr>';
        }
    };
        echo '</tbody>';
    echo '</table>';
};

function validation(){

    $child_name = $_REQUEST['child_name'];
    $child_birthday = $_REQUEST['child_birthday'];
    $tutor_name = $_REQUEST['tutor_name'];
    $tutor_phone = $_REQUEST['tutor_phone'];
    $tutor_email = $_REQUEST['tutor_email'];
    $goBack = false;

    if(preg_match("/^[a-zA-Z-' ]*$/",$child_name) === 0 || !$child_name){
        echo 'O nome da criança é invalido, verifique se esta bem escrito ou se o campo ten algun valor'.'<br>';
        $goBack = true;
    }
    if(preg_match("/^[a-zA-Z-' ]*$/",$tutor_name) === 0 || !$tutor_name){
        echo 'O nome do encarregado é invalido, verifique se esta bem escrito ou se o campo ten algun valor'.'<br>';
        $goBack = true;
    }
    if (validateDate($child_birthday, 'Y-m-d') == false || !$child_birthday){
        echo 'A data de nascimento é invalida, verifique se esta no formato AAAA-MM-DD ou se o campo ten algun valor'.'<br>';
        $goBack = true;
    }
    if (preg_match('/^[0-9]{9}+$/', $tutor_phone) === 0 || !$tutor_phone){
        echo 'O numero de telemovel é invalido, verifique se o numero ten 9 digitos ou se o campe ten algun valor'.'<br>';
        $goBack = true;
    }
    if ($goBack == true){
        goBack();
    }
    else{
        $current_page = currentPage();
        echo '<h3>Dados de registo - validação</h3>';
        echo 'Estamos prestes a inserir os dados abaixo na base de dados. Confirma que os dados estão correctos e pretende submeter os mesmos?';
        echo '<ul>';
            echo '<li>'.'Nome da Criança: '.$child_name.'</li>';
            echo '<li>'.'Data de Nascimento: '.$child_birthday.'</li>';
            echo '<li>'.'Nome do Encarregado: '.$tutor_name.'</li>';
            echo '<li>'.'Telemovel do Encarregado: '.$tutor_phone.'</li>';
            echo '<li>'.'Email do Encarregado: '.$tutor_email.'</li>';
        echo '</ul>';

        echo "<form method='post' action='$current_page'>";
            echo "<input type='hidden' name='child_name' value='$child_name'>";
            echo "<input type='hidden' name='child_birthday' value='$child_birthday'>";
            echo "<input type='hidden' name='tutor_name' value='$tutor_name'>";
            echo "<input type='hidden' name='tutor_phone' value='$tutor_phone'>";
            echo "<input type='hidden' name='tutor_email' value='$tutor_email'>";
            echo '<input type="hidden" name="estado" value="inserir"><br>';
            echo '<input type="submit" value="Sumeter">'.'<br>';
                  goBack();
        echo "</form>";
    }
}

function insertData(){
    $child_name = $_REQUEST['child_name'];
    $child_birthday = $_REQUEST['child_birthday'];
    $tutor_name = $_REQUEST['tutor_name'];
    $tutor_phone = $_REQUEST['tutor_phone'];
    $tutor_email = $_REQUEST['tutor_email'];

    $query_insert_data = "INSERT INTO child (name, birth_date, tutor_name, tutor_phone, tutor_email)
                          VALUES ('$child_name', '$child_birthday', '$tutor_name', '$tutor_phone', '$tutor_email')";

    if(mysqli_query(connection(),$query_insert_data)){
        echo '<h3>Dados de registo - inserção</h3>';
        echo 'Inseriu os dados de registo com sucesso.'.'<br>';
        echo '<a href = "wordpress/gestao-de-registos">Continuar</a>';
    }
    else{
        echo 'Error na inserção dos dados'.'<br>';
        goBack();
    }
}

function createForm(){
    $current_page = currentPage();
    echo '<h3>Dados de registo - Introdução</h3>';
    echo '<p>Introduza os dados pessoais básicos da criança:</p>';
    echo "<form method='post' action='$current_page'>";
        echo 'Nome completo: <input type="text" name="child_name"><br>';
        echo 'Data de nascimento: <input type="text" name="child_birthday"><br>';
        echo 'Nome completo do encarregado de educação: <input type="text" name="tutor_name"><br>';
        echo 'Telefone do encarregado de educação: <input type="text" name="tutor_phone"><br>';
        echo 'Endereço de e-mail do tutor: <input type="text" name="tutor_email"><br>';
        echo '<input type="hidden" name="estado" value="validar"><br>';
        echo '<input type="submit">';
    echo '</form>';

}

function main (){
    if(is_user_logged_in() == true && current_user_can("manage_records")){
        if(empty($_REQUEST['estado'])){
            createTable();
            createForm();
        }
        elseif($_REQUEST['estado'] == "validar"){
            validation();
        }
        elseif($_REQUEST['estado'] == "inserir"){
            insertData();
        }
        return;
    };
    echo "Não tem autorização para aceder a esta página";
};
main();



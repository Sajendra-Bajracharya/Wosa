<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER["REQUEST_METHOD"]=="POST") {
    if (isset($_POST['Add_To_Cart'])) {
        // Get redirect URL from form or use referer
        $redirect_url = isset($_POST['redirect_url']) ? $_POST['redirect_url'] : (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.html');
        
        if (isset($_SESSION['cart'])) {
            $myitems=array_column($_SESSION['cart'], 'Item_Name');
            if (in_array($_POST['Item_Name'], $myitems)) {
                // Use URL parameter instead of alert
                $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'cart_message=already_added';
                header("Location: $redirect_url");
                exit;
            } else {
                $count=count($_SESSION['cart']);
                $_SESSION['cart'][$count]=array('Item_Name'=>$_POST['Item_Name'],'Price'=>$_POST['Price'],'Quantity'=>1);
                // Use URL parameter instead of alert
                $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'cart_message=added';
                header("Location: $redirect_url");
                exit;
            }
        } else {
            $_SESSION['cart'][0]=array('Item_Name'=>$_POST['Item_Name'],'Price'=>$_POST['Price'],'Quantity'=>1);
            // Use URL parameter instead of alert
            $redirect_url .= (strpos($redirect_url, '?') !== false ? '&' : '?') . 'cart_message=added';
            header("Location: $redirect_url");
            exit;
        }
    }
    if (isset($_POST['Remove_Item'])) {
        foreach ($_SESSION['cart'] as $key => $value) {
            if ($value['Item_Name']==$_POST['Item_Name']) {
                unset($_SESSION['cart'][$key]);
                $_SESSION['cart']=array_values($_SESSION['cart']);
                // Use URL parameter instead of alert
                header("Location: mycart.php?cart_message=removed");
                exit;
            }
        }
    }
    if (isset($_POST['Mod_Quantity'])) {
        foreach ($_SESSION['cart'] as $key => $value) {
            if ($value['Item_Name']==$_POST['Item_Name']) {
                $_SESSION['cart'][$key]['Quantity']=$_POST['Mod_Quantity'];
                header("Location: mycart.php");
                exit;
            }
        }
    }
}

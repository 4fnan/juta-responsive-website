<?php
require_once('model/products.php');


class basket
{

    private $db;

    /**
     * creates an instance of database class to connect to the database
     */
    public function __construct()
    {
        $dbInstance= Database::getInstance();
        $this->db= $dbInstance->getdbConnection();
    }

    public function addBasketOrder($custId)
    {
        $product = new products();
        $orderTotal = 0;

        $sqlQuery = "INSERT INTO Orders(customerId,orderTotal, orderDate)"." VALUES(?, ?, CURRENT_TIMESTAMP)";
        $statement = $this->db->prepare($sqlQuery);
        $statement->bindValue(1, $custId);
        $statement->bindValue(2, 0);
        $statement->execute();


        if ($statement->rowCount() == 1) {

            $sqlQuery = "SELECT * FROM Orders WHERE customerId = $custId ORDER BY orderId DESC";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);


            for($i = 0; $i < count($_SESSION['basketProds']); $i++) {

                $prodInfo = $product->getProductInfo($_SESSION['basketProds'][$i]);
                $resultProd = $prodInfo->fetch(PDO::FETCH_ASSOC);

                $sqlQuery = "INSERT INTO orderLines(orderId, customerId, productId, Quantity, shoeSize, lineCost)"
                    . "VALUES (?, ?, ?, ?, ?, ?)";
                $statement = $this->db->prepare($sqlQuery);

                $linePrice =  $resultProd['Price'];
                $lineQty =  $_SESSION['basketProdsQty'][$i];
                $lineTotal = $linePrice*$lineQty;

                $statement->bindValue(1, $result['orderId']);
                $statement->bindValue(2, $custId);
                $statement->bindValue(3,  $_SESSION['basketProds'][$i]);
                $statement->bindValue(4,  $_SESSION['basketProdsQty'][$i]);
                $statement->bindValue(5,  $_SESSION['basketProdsSize'][$i]);
                $statement->bindValue(6, $lineTotal);

                $statement->execute();
                $orderTotal = $orderTotal+$lineTotal;

                $qty =  $_SESSION['basketProdsQty'][$i];
                $prodId =  $_SESSION['basketProds'][$i];

                $sqlQuery = "UPDATE Products SET Quantity = Quantity - $qty WHERE productId = $prodId";
                $statement = $this->db->prepare($sqlQuery);
                $statement->execute();
            }
            $orderId = $result['orderId'];

            $sqlQuery = "UPDATE Orders SET orderTotal = $orderTotal WHERE orderId = $orderId";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();

            //reset arrays
            $_SESSION['basketProds'] = array();
            $_SESSION['basketProdsQty'] = array();
            $_SESSION['basketProdsSize'] = array();

        }

    }

    public function removeBasket($arrayIndex)
    {
        //update the prod qty in the db
        $id = $_SESSION['basketProds'][$arrayIndex];
        $qty = $_SESSION['basketProdsQty'][$arrayIndex];
        $sqlQuery = "UPDATE Products SET Quantity = Quantity + $qty WHERE productId = $id ";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();

        //unset arrays
        unset($_SESSION['basketProds'][$arrayIndex]);
        unset($_SESSION['basketProdsQty'][$arrayIndex]);
        unset($_SESSION['basketProdsSize'][$arrayIndex]);
        //rebuilding/fixing the broken arrays
        $_SESSION['basketProds'] = array_values($_SESSION['basketProds']);
        $_SESSION['basketProdsQty'] = array_values($_SESSION['basketProdsQty']);
        $_SESSION['basketProdsSize'] = array_values($_SESSION['basketProdsSize']);
    }

}

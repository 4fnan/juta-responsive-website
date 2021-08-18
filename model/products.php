<?php


class products
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

 // gets the product names to be used in search suggestions using jquery
    public function getProdNames()
    {
        return null;
    }

    public function getSubCategories($category)
    {
        $sqlQuery = "SELECT * FROM Category WHERE category = '$category'";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();

        return $statement;
    }

    public function getNewArrivals()
    {
        $sqlQuery = "SELECT * FROM Products ORDER BY productId DESC LIMIT 9";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();

        return $statement;
    }

    public function getProducts($id, $cat)
    {
        if ($id == "")
        {
            $sqlQuery = "SELECT * FROM Category WHERE category = '$cat'";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();

            $catIdArray= array();
            $n = $statement->rowCount();
            for ($i=0; $i<$n; $i++) {
                $result = $statement->fetch(PDO::FETCH_ASSOC);
                $catIdArray[] = $result['categoryId'];
            }

            $catIds = implode(', ', $catIdArray);

            $sqlQuery = "SELECT * FROM Products WHERE categoryId IN ($catIds)";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();

            return $statement;

        }else {
            $sqlQuery = "SELECT * FROM Products WHERE categoryId = '$id'";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();

            return $statement;

        }

    }

    public function getProductCategory($catId)
    {
        $sqlQuery = "SELECT * FROM Category WHERE categoryId=$catId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
        return $statement;

    }

    public function getProductInfo($prodId)
    {
        $sqlQuery = "SELECT * FROM Products where productId = '$prodId'";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
        return $statement;

    }
    public function getSize($prodId)
    {
        $sqlQuery = "SELECT * FROM shoeSize where productId = $prodId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
        return $statement;

    }

    public function getCustomer($usrId)
    {
        $sqlQuery = "SELECT * FROM Customers WHERE userId = $usrId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
        return $statement;

    }

    public function addToBasket($prodId, $prodQty, $prodSize){

        array_push($_SESSION['basketProds'], $prodId);
        array_push($_SESSION['basketProdsQty'],$prodQty );
        array_push($_SESSION['basketProdsSize'], $prodSize);
//log out issue fix
//        for($i = 0 ; $i< count($_SESSION['basketProds']); $i++) {
//            "UPDATE Products SET Quantity = Quantity + $_SESSION['basketProdsQty'][$i] WHERE productId = $_SESSION['basketProds'][$i]";
//       }
        $sqlQuery = "UPDATE Products SET Quantity = Quantity - $prodQty WHERE productId = $prodId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
    }

    public function addOrder($custId, $price, $prodId, $qty, $size)
    {
        $sqlQuery = "INSERT INTO Orders(customerId,orderTotal, orderDate)"." VALUES(?, ?, CURRENT_TIMESTAMP)";
        $statement = $this->db->prepare($sqlQuery);
        $statement->bindValue(1, $custId);
        $statement->bindValue(2, $price);
        $statement->execute();


        if ($statement->rowCount() == 1) {

            $sqlQuery = "SELECT * FROM Orders WHERE customerId = $custId ORDER BY orderId DESC";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);

            $sqlQuery = "INSERT INTO orderLines(orderId, customerId, productId, Quantity, shoeSize, lineCost)"
            ."VALUES (?, ?, ?, ?, ?, ?)";
            $statement = $this->db->prepare($sqlQuery);

            $statement->bindValue(1, $result['orderId']);
            $statement->bindValue(2, $custId);
            $statement->bindValue(3, $prodId);
            $statement->bindValue(4, $qty);
            $statement->bindValue(5, $size);
            $statement->bindValue(6, $price);

            $statement->execute();

            $sqlQuery = "UPDATE Products SET Quantity = Quantity - '$qty' WHERE productId = '$prodId'";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();


        }

    }


    public function getCustOrders($custId){

        $sqlQuery = "SELECT * FROM Orders where CustomerId = $custId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();

        return $statement;
    }

    public function getOrderLine($orderId){
        $sqlQuery = "SELECT * FROM orderLines where orderId = $orderId";
        $statement = $this->db->prepare($sqlQuery);
        $statement->execute();
        return $statement;
    }



}

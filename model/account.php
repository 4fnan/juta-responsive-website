<?php
require_once('model/database.php');

class account
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

    /**
     * login function allows users to login
     *
     * @param $name
     * @param $pass
     * @param $email
     */
    public function login($name,$pass,$email)
    {
        if(!empty($name) && !empty($pass))
        {
            $sqlQuery = "SELECT * FROM Users WHERE username = ? OR email = ? AND password = ?";
            $statement = $this->db->prepare($sqlQuery);
            $statement->bindParam(1, $name);
            $statement->bindParam(2, $email);
            $statement->bindParam(3, $pass);
            $statement->execute();



            if($statement->rowCount() == 1)
            {
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                    $_SESSION['login'] = $_POST['uname'];
                    $_SESSION['id'] = $result['userId'];
                    $_SESSION['basketProds'] = array();
                    $_SESSION['basketProdsQty'] = array();
                    $_SESSION['basketProdsSize'] = array();
//                    header("Location: index.php");?>

<script> window.location.href = 'index.php';</script>

<?php


            }
            else
            {
                echo '<div id="content">Incorrect data entered</div>';
            }
        }
        else
        {
            echo '<div id="content">All fields are required</div>';
        }
    }

    /**
     * @param $username
     * @param $password
     * @param $email
     * @param $name
     *
     * Register function, allows new users to register and add them to the database
     */
    public function register($username, $password, $email, $fname, $lname, $phone, $addr1, $addr2, $city, $postcode)
    {
//        $hash = password_hash($password, PASSWORD_DEFAULT);

        if($username != "admin"){
        if(!empty($username) && !empty($password) && !empty($email))
        {
            $sqlQuery = "INSERT INTO Users (Username, Password, Email)"
                ."VALUES(?, ?, ?)";
            $statement = $this->db->prepare($sqlQuery);
            $statement->bindValue(1, $username);
            $statement->bindValue(2, $password);
            $statement->bindValue(3, $email);

            $statement->execute();

            if ($statement->rowCount() == 1)
            {

                //Selects the userID from inserted user above
                $sqlQuery = "SELECT userId FROM Users WHERE Username = '$username'";
                $statement = $this->db->prepare($sqlQuery);
                $statement->execute();
                $result = $statement->fetch(PDO::FETCH_ASSOC);

                //inserts the data including the userid selected above, in customers table
                $sqlQuery = "INSERT INTO Customers(userId, firstName, lastName, phone, addressLine1, addressLine2, City, Postcode)"
                    ."VALUES(?, ?, ?, ?, ?, ?, ?, ?)";

                $statement = $this->db->prepare($sqlQuery);
                $statement->bindValue(1, $result['userId']);
                $statement->bindValue(2, $fname);
                $statement->bindValue(3, $lname);
                $statement->bindValue(4, $phone);
                $statement->bindValue(5, $addr1);
                $statement->bindValue(6, $addr2);
                $statement->bindValue(7, $city);
                $statement->bindValue(8, $postcode);


                $statement->execute();

//                $result = $statement->fetch(PDO::FETCH_ASSOC);
                    if ($statement->rowCount() == 1) {
                        $_SESSION['login'] = $username;
                        $_SESSION['id'] = $result['userId'];
                        $_SESSION['basketProds'] = array();
                        $_SESSION['basketProdsQty'] = array();
                        $_SESSION['basketProdsSize'] = array();



                ?>
                <script> window.location.href = 'index.php';</script>
                <?php
                    }
                    else
                    {
                        echo '<div id="content">Something went wrong, please try again.</div>';
                    }
            }
        }
        else
        {
            echo '<div id="content">Something went wrong, please try again.</div>';
        }

        }

        else
        {
            echo '<div id="content">Username not allowed.</div>';
        }

    }

    /**
     * @return boolean
     *
     * logout function allows logged in users to log out
     *
     */
    public function logout()
    {
        for($i = 0 ; $i< count($_SESSION['basketProds']); $i++)
        {
            $qty = $_SESSION['basketProdsQty'][$i];
            $id = $_SESSION['basketProds'][$i];

            $sqlQuery = "UPDATE Products SET Quantity = Quantity + $qty WHERE productId = $id ";
            $statement = $this->db->prepare($sqlQuery);
            $statement->execute();
        }

        // Destroy and unset active session
        session_destroy();


        unset($_SESSION['login']);
        unset($_SESSION['basketProds']);
        unset($_SESSION['basketProdsQty']);
        unset($_SESSION['basketProdsSize']);
        unset($_SESSION['id']);

        header("location:index.php");
        return true;
    }
}

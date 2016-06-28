<?php
echo "<?php\n";
?>

namespace <?=$namespace?>;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Schema\Schema;

class <?=$className?> extends AbstractMigration
{

    protected $username = '<?=$username?>';
    
    protected $seedsValues = array(
        '<?=$username?>', '<?=$password?>', '<?=$salt?>', '', '', '', '', '', 'date'
    );
    
    protected $seedsKeys = array(
        'username', 'password', 'salt', 'givenname', 'surname', 'mail', 'entity', 'manager_username', 'creation_date',
    );

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $idUser = $this->insertUser();

        $this->insertUserRole($idUser);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->deleteUserRole();

        $this->deleteUser();
    }

    protected function insertUser()
    {
        $this->seedsValues = str_replace('date', date('Y-m-d H:i:s'), $this->seedsValues);
        
        $this->connection->insert('oft_users', array_combine($this->seedsKeys, $this->seedsValues));

        return $this->connection->lastInsertId();
    }

    protected function insertUserRole($idUser)
    {
        $idGroup = $this->getIdAdminGroup();

        return $this->connection->insert('oft_acl_role_user', array(
            'id_user' => $idUser,
            'id_acl_role' => $idGroup,
        ));
    }

    protected function deleteUserRole()
    {
        $idUser = $this->getIdUser();

        $idGroup = $this->getIdAdminGroup();

        return $this->connection->delete('oft_acl_role_user', array(
            'id_user' => $idUser,
            'id_acl_role' => $idGroup,
        ));
    }

    protected function deleteUser()
    {
        return $this->connection->delete('oft_users', array(
            'username' => $this->username,
        ));
    }

    protected function getIdAdminGroup()
    {
        $queryBuilder = new QueryBuilder($this->connection);

        $queryBuilder->select('id_acl_role')
            ->from('oft_acl_roles')
            ->where('name = :name')
            ->setParameter('name', 'administrators');

        $stmt = $queryBuilder->execute();

        $row = $stmt->fetch();

        return $row['id_acl_role'];
    }

    protected function getIdUser()
    {
        $queryBuilder = new QueryBuilder($this->connection);

        $queryBuilder->select('id_user')
            ->from('oft_users')
            ->where('username = :username')
            ->setParameter('username', $this->username);

        $stmt = $queryBuilder->execute();

        $row = $stmt->fetch();

        return $row['id_user'];
    }

}

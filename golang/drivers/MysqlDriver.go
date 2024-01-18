package drivers

import (
	"fmt"
	"log"

	"github.com/joho/godotenv"
	"github.com/reza-neyrami/liveclass/App/drivers/SqlConfig"
	"gorm.io/driver/mysql"
	"gorm.io/gorm"
)

type MYSQLDatabase interface {
	Connect() (*MysqlDB, error)
	CloseDb()
}

// MySQLDriver struct defines necessary parameters for mysql connection
type MysqlDB struct {
	*gorm.DB
}

// Connect function establishes a connection to a mysql database and returns the gorm.DB object.
func (d *MysqlDB) Connect() (*MysqlDB, error) {
	err := godotenv.Load()
	if err != nil {
		log.Fatalf("Error loading .env file: %v", err)
	}
	cfg := SqlConfig.MySQLConfig{}

	dsn := cfg.Config()
	fmt.Println(dsn)
	db, err := gorm.Open(mysql.Open(dsn.(string)), &gorm.Config{})
	if err != nil {
		return nil, fmt.Errorf("failed to initialize GORM: %v", err)
	}

	return &MysqlDB{db}, err
}

func (d *MysqlDB) CloseDb() {
	con, _ := d.DB.DB()
	con.Close()
}

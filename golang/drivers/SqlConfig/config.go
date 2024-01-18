package SqlConfig

import (
	"fmt"
	"log"
	"os"
	"strconv"

	"github.com/mtfelian/utils"
	"github.com/reza-neyrami/liveclass/Config"
)

type DriverConfig interface {
	DriverName() string           // نام درایور
	Config() interface{}          // اطلاعات کانفیگ درایور
	LoadConfig() *Config.DBConfig // اطلاعات کانفیگ درایور
}

type MySQLConfig struct {
	Host           string
	Port           uint16
	User           string
	Password       string
	DBName         string
	TimeoutSeconds int
	ReadTimeout    int
	WriteTimeout   int
}

func (mc *MySQLConfig) DriverName() string {
	return "mysql"
}

func (mc *MySQLConfig) Config() interface{} {
	return mc.LoadConfig()
}

func (mc *MySQLConfig) LoadConfig() string {
	return fmt.Sprintf(
		"%s:%s@tcp(%s:%d)/%s?parseTime=true",
		os.Getenv("DB_USER"),
		os.Getenv("DB_PASS"),
		os.Getenv("DB_HOST"),
		3306,
		os.Getenv("DB_NAME"),
	)
}

type PostgresConfig struct {
	Host           string
	Port           uint16
	User           string
	Password       string
	DBName         string
	TimeoutSeconds int
	ReadTimeout    int
	WriteTimeout   int
}

func (pc *PostgresConfig) DriverName() string {
	return "postgres"
}

func (pc *PostgresConfig) Config() interface{} {
	return pc.LoadConfig()
}

func (pc *PostgresConfig) LoadConfig() string {
	port, err := strconv.Atoi(os.Getenv("PG_PORT"))
	fmt.Println(port)
	if err != nil {
		log.Fatal(err)
	}
	return fmt.Sprintf("host=%s port=%d user=%s password=%s dbname=%s sslmode=%s",
		os.Getenv("PG_HOST"),
		port,
		os.Getenv("PG_USERNAME"),
		os.Getenv("PG_PASSWORD"),
		os.Getenv("PG_DBNAME"),
		"disable", // اضافه کردن sslmode=disable
	)

}

func StringToUint(s string) uint16 {
	u, err := utils.StringToUint(s)
	if err != nil {
		log.Fatalf("Failed to parse port number %v: %v", s, err)
	}
	return uint16(u)
}

// PostgresDriver struct defines necessary parameters for postgres connection

// Connect function establishes a connection to a postgres database and returns the gorm.DB object.

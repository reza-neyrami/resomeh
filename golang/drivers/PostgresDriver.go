package drivers

import (
	"fmt"
	"log"

	"github.com/jmoiron/sqlx"
	"github.com/joho/godotenv"
	_ "github.com/lib/pq"
	"github.com/reza-neyrami/liveclass/App/drivers/SqlConfig"
)

type PGDatabase interface {
	Connect() (*PostgresDB, error)
	QueryDB(query string, args ...interface{}) (*sqlx.Rows, error)
	CloseDB() error
	GetDb() *sqlx.DB
}

type PostgresDB struct {
	DB *sqlx.DB
}

// Connect function establishes a connection to a postgres database and returns the sql.DB object.
func (pc *PostgresDB) Connect() (*PostgresDB, error) {
	err := godotenv.Load("./../.env")
	if err != nil {
		log.Fatalf("Error loading .env file: %v", err)
	}
	cfg := SqlConfig.PostgresConfig{}
	dsn := cfg.Config()

	db, err := sqlx.Connect("postgres", dsn.(string))

	if err != nil {
		return nil, fmt.Errorf("failed to initialize database: %v", err)
	}

	db.SetMaxIdleConns(10)
	db.SetMaxOpenConns(100)
	log.Println("Db connection established")
	return &PostgresDB{db}, err
}

func (pc *PostgresDB) QueryDB(query string, args ...interface{}) (*sqlx.Rows, error) {
	rows, err := pc.DB.Queryx(query, args...)
	if err != nil {
		return nil, err
	}
	return rows, nil
}

func (pc *PostgresDB) GetDb() *sqlx.DB {
	return pc.DB
}

func (pc *PostgresDB) CloseDB() error {
	err := pc.DB.Close()
	if err != nil {
		return err
	}
	return nil
}

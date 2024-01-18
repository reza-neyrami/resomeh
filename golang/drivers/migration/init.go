package migration

import (
	"fmt"

	"github.com/reza-neyrami/liveclass/Config"
	"github.com/reza-neyrami/liveclass/Config/constants"
	"github.com/reza-neyrami/liveclass/Modules/User/models"
	"github.com/reza-neyrami/liveclass/pkg/logging"
	"gorm.io/driver/postgres"
	"gorm.io/gorm"
)

var logger = logging.NewLogger(Config.GetConfig())

func Up() {
	var cfg *Config.Config

	cnn := fmt.Sprintf("host=%s port=%s user=%s password=%s dbname=%s sslmode=%s TimeZone=Asia/Tehran",
		cfg.Postgres.Host, cfg.Postgres.Port, cfg.Postgres.User, cfg.Postgres.Password,
		cfg.Postgres.DbName, cfg.Postgres.SSLMode)
	
	database, err := gorm.Open(postgres.Open(cnn), &gorm.Config{})
	if err != nil {
		logger.Error(logging.Postgres, logging.Migration, err.Error(), nil)
		return
	}

	fmt.Println(database)
	createTables(database)
	createDefaultUserInformation(database)
	logger.Info(logging.Postgres, logging.Migration, "UP", nil)
}

func createTables(database *gorm.DB) {
	err := database.AutoMigrate(&models.User{})
	if err != nil {
		logger.Error(logging.Postgres, logging.Migration, err.Error(), nil)
	}
	logger.Info(logging.Postgres, logging.Migration, "tables created", nil)
}

func createDefaultUserInformation(database *gorm.DB) {
	u := models.User{FirstName: constants.DefaultUserName, Email: constants.DefaultEmail}
	createAdminUserIfNotExists(database, &u)
}

func createAdminUserIfNotExists(database *gorm.DB, u *models.User) {
	var exists int64
	database.Model(&models.User{}).Where("phone = ?", u.Phone).Count(&exists)
	if exists == 0 {
		database.Create(&u)
	}
}

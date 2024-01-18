package api

import (
	"fmt"
	"log"

	"github.com/gin-gonic/gin"
	"github.com/gin-gonic/gin/binding"
	"github.com/go-playground/validator/v10"
	"github.com/reza-neyrami/liveclass/App/drivers"
	"github.com/reza-neyrami/liveclass/Config"
	"github.com/reza-neyrami/liveclass/Modules/Api/routers"
	validation "github.com/reza-neyrami/liveclass/Modules/Api/validations"
	"github.com/reza-neyrami/liveclass/pkg/logging"
)

func InitServer(config *Config.Config, logger logging.Logger, db *drivers.PostgresDB) {
	r := gin.Default()
	r.Use(gin.Logger(), gin.Recovery())
	RegisterValidators()
	v1 := r.Group("api/v1")
	{
		auth := v1.Group("user")
		routers.Auth(auth, config, db, logger)

		posts := v1.Group("posts")
		routers.Posts(posts, config)
	}

	err := r.Run(fmt.Sprintf(":%s", config.Server.InternalPort))
	if err != nil {
		return
	}
}

func RegisterValidators() {
	val, ok := binding.Validator.Engine().(*validator.Validate)
	if ok {
		err := val.RegisterValidation("password", validation.PasswordValidator, true)
		if err != nil {
			// logger.Error(logging.Validation, logging.Startup, err.Error(), nil)
			log.Fatal("validation failed")
		}
	}
}

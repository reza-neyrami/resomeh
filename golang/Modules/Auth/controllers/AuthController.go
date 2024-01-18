package controllers

import (
	"net/http"

	"github.com/gin-gonic/gin"
	request "github.com/reza-neyrami/liveclass/Modules/Api/Request"
	authservice "github.com/reza-neyrami/liveclass/Modules/Api/services/AuthService"
	"github.com/reza-neyrami/liveclass/Modules/Auth/interfaces"
	"github.com/reza-neyrami/liveclass/Modules/User/models"
)

type AuthController struct {
	authServices authservice.AuthService
}

func NewAuthController(authServices authservice.AuthService) *AuthController {
	return &AuthController{
		authServices: authServices,
	}
}

func (c *AuthController) LoginPhone(ctx *gin.Context) {
	var req request.LoginPhoneRequest

	if err := ctx.ShouldBindJSON(&req); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}
	
	username := req.Username
	authRepo, err := c.authServices.GetAuthRepository(username)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"message": err.Error()})
		return
	}
	service, err := c.authServices.GetAuthService(username)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"message": err.Error()})
		return
	}
	response := authRepo.Login(username, service.(interfaces.VerifyServiceInterface))

	ctx.JSON(http.StatusOK, gin.H{"response": response})
}

func (c *AuthController) VerifyCode(ctx *gin.Context) {
	var req request.VerfriCodeActivationRequest
	if err := ctx.ShouldBindJSON(&req); err != nil {
		ctx.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	authRepo, _ := c.authServices.GetAuthRepository(req.Username)
	response, err := authRepo.VerifyCodes(req.Username, req.Code)
	if err != nil {
		ctx.JSON(http.StatusInternalServerError, gin.H{"message": err.Error()})
		return
	}
	if response["user"] != nil {
		user := response["user"]
		token, _ := models.CreateToken(user)
		ctx.JSON(http.StatusOK, gin.H{
			"auth":         user,
			"message":      response["message"],
			"checkout":     response["checkout"],
			"username":     req.Username,
			"codephone":    response["codephone"],
			"access_token": token,
		})
	}

}



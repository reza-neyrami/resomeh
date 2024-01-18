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

// func (c *AuthController) Registeration(request *AuthUserRegisterRequest) map[string]interface{} {
// 	username := request.Username
// 	authRepo, _ := c.authServices.GetAuthRepository(username)
// 	response, _ := authRepo.Register(request)
// 	if response["user"] != nil {
// 		user := response["user"].(User)
// 		token, _ := user.CreateToken("Register")
// 		return map[string]interface{}{
// 			"message":      response["message"],
// 			"auth":         user,
// 			"access_token": token,
// 			"login":        true,
// 		}
// 	}
// 	return response
// }

// func (c *AuthController) Logout(request *Request) (map[string]interface{}, int) {
// 	request.User.Token.Revoke()
// 	return map[string]interface{}{"message": "logout is SuccessFully"}, 200
// }

// func (c *AuthController) UploadImagesLogin(request *UploadImageRequstHome) (map[string]interface{}, int) {
// 	log.Println("Public path: " + publicPath())
// 	bannerName := time.Now().Format("2006-01/02") + "/" + "_Banner"
// 	banner := request.File["banner"]
// 	url, _ := storage.Disk("banners").Put(bannerName, banner)
// 	return map[string]interface{}{
// 		"message": "اپلود بنر با موفقیت انجام شد",
// 		"banner":  "/api/banners/" + url,
// 	}, 200
// }

package controllers

import (
	"errors"
	"net/http"
	"strconv"

	"github.com/gin-gonic/gin"
	helper "github.com/reza-neyrami/liveclass/App/helpers"
	"github.com/reza-neyrami/liveclass/Modules/Auth/interfaces"
	"github.com/reza-neyrami/liveclass/Modules/User/models"
	"gorm.io/gorm"
)

type UserController struct {
	userRepository interfaces.UserRepository
}

func NewUserController(userRepository interfaces.UserRepository) *UserController {
	return &UserController{
		userRepository: userRepository,
	}
}

// GetAll returns a list of all users
func (ctrl *UserController) GetAll(ctx *gin.Context) {
	if ctrl.userRepository == nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, errors.New("userRepository is not initialized"))
		ctx.JSON(http.StatusInternalServerError, response)
		return
	}
	users, err := ctrl.userRepository.FindAll()
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, err)
		ctx.JSON(http.StatusInternalServerError, response)
		return
	}
	response := helper.GenerateBaseResponse(users, true, helper.Success)
	ctx.JSON(http.StatusOK, response)
}

// GetByID returns a single user by its ID
func (ctrl *UserController) GetByID(ctx *gin.Context) error {
	idStr := ctx.Param("id")
	id, err := strconv.Atoi(idStr)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, errors.New("Invalid ID"))
		ctx.JSON(http.StatusBadRequest, response)
		return nil
	}

	user, err := ctrl.userRepository.Get(uint(id))
	if err != nil {
		if errors.Is(err, gorm.ErrRecordNotFound) {
			response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, errors.New("User not found"))
			ctx.JSON(http.StatusNotFound, response)
			return nil
		}
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, err)
		ctx.JSON(http.StatusInternalServerError, response)
		return nil
	}

	response := helper.GenerateBaseResponse(user, true, helper.Success)
	ctx.JSON(http.StatusOK, response)
	return nil
}

func (ctrl *UserController) CreateUser(ctx *gin.Context) {
	var user models.User
	err := ctx.Bind(&user)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.NotFoundError, err)
		ctx.JSON(http.StatusBadRequest, response)
		return
	}
	err = ctrl.userRepository.Store(&user)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, err)
		ctx.JSON(http.StatusInternalServerError, response)
		return
	}
	response := helper.GenerateBaseResponse(user, true, helper.Success)
	ctx.JSON(http.StatusCreated, response)
}

func (ctrl *UserController) UpdateUser(ctx *gin.Context) {
	idStr := ctx.Param("id")
	id, err := strconv.Atoi(idStr)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, errors.New("Invalid ID"))
		ctx.JSON(http.StatusBadRequest, response)
		return
	}
	var user models.User
	err = ctx.Bind(&user)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.ValidationError, err)
		ctx.JSON(http.StatusBadRequest, response)
		return
	}
	user.ID = uint(id)
	err = ctrl.userRepository.Update(user.ID, &user)
	if err != nil {
		if errors.Is(err, gorm.ErrRecordNotFound) {
			response := helper.GenerateBaseResponseWithError(nil, false, helper.NotFoundError, errors.New("User not found"))
			ctx.JSON(http.StatusNotFound, response)
		} else {
			response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, err)
			ctx.JSON(http.StatusInternalServerError, response)
		}
		return
	}
	response := helper.GenerateBaseResponse(user, true, helper.Success)
	ctx.JSON(http.StatusCreated, response)
}

func (ctrl *UserController) DeleteUser(ctx *gin.Context) {
	idStr := ctx.Param("id")
	id, err := strconv.Atoi(idStr)
	if err != nil {
		response := helper.GenerateBaseResponseWithError(nil, false, helper.ValidationError, errors.New("User not found"))
		ctx.JSON(http.StatusBadRequest, response)
		return
	}
	err = ctrl.userRepository.Delete(uint(id))
	if err != nil {
		if errors.Is(err, gorm.ErrRecordNotFound) {
			response := helper.GenerateBaseResponseWithError(nil, false, helper.NotFoundError, errors.New("User not found"))
			ctx.JSON(http.StatusNotFound, response)
		} else {
			response := helper.GenerateBaseResponseWithError(nil, false, helper.InternalError, err)
			ctx.JSON(http.StatusInternalServerError, response)
		}
		return
	}
	response := helper.GenerateBaseResponse(nil, true, helper.Success)
	ctx.JSON(http.StatusCreated, response)
}

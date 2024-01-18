package authservice

import (
	"errors"
	"fmt"
	"log"

	"github.com/reza-neyrami/liveclass/Modules/Auth/interfaces"
)

type AuthService struct {
	services map[string]interface{}
}

func NewAuthService(authServicePhone *AuthServicePhone, authServiceMail *AuthServiceMail) *AuthService {
	return &AuthService{
		services: map[string]interface{}{
			"phone": authServicePhone,
			"mail":  authServiceMail,
		},
	}
}

func (s *AuthService) GetAuthRepository(username string) (interfaces.AuthRepositoryInterface, error) {
	for key, service := range s.services {
		switch v := service.(type) {
		case interfaces.AuthServiceInterface:
			if v.MatchesPattern(username) {
				return v.GetAuthRepository(), nil
			}
		default:
			log.Printf("service type '%s' not supported", key) // اضافه کردن این خط
			return nil, fmt.Errorf("service type '%s' not supported", key)
		}
	}
	log.Println("نام کاربری نامعتبر است.") // اضافه کردن این خط
	return nil, errors.New("نام کاربری نامعتبر است.")
}

func (s *AuthService) GetAuthService(username string) (interfaces.VerifyServiceInterface, error) {
	for key, service := range s.services {
		switch v := service.(type) {
		case interfaces.AuthServiceInterface:
			if v.MatchesPattern(username) {
				return v.GetAuthService(), nil
			}
		default:
			log.Printf("service type '%s' not supported", key) // اضافه کردن این خط
			return nil, fmt.Errorf("service type '%s' not supported", key)
		}
	}
	log.Println("نام کاربری نامعتبر است.") // اضافه کردن این خط
	return nil, errors.New("نام کاربری نامعتبر است.")
}

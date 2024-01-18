package request

import (
	"fmt"
	"github.com/go-playground/validator/v10"
	"regexp"
)

type LoginPhoneRequest struct {
	Username string `validate:"required"`
}

func (r *LoginPhoneRequest) Validate() error {
	validate := validator.New()

	err := validate.Struct(r)
	if err != nil {
		return err
	}

	emailPattern := `^[\w-]+(\.[\w-]+)*@([\w-]+\.)+[a-zA-Z]{2,7}$`
	iranianPhonePattern := `^(0098|\+?98|0)9\d{9}$`

	if matched, _ := regexp.MatchString(emailPattern, r.Username); !matched {
		if matched, _ := regexp.MatchString(iranianPhonePattern, r.Username); !matched {
			return fmt.Errorf("نام کاربری باید یک ایمیل یا یک شماره تلفن ایرانی باشد")
		}
	}

	return nil
}



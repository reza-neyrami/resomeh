package irepositories

import (
	"github.com/reza-neyrami/liveclass/Modules/Api/models"
)

type CountryRepository interface {
	GetCountries() ([]**models.Country, error)
	GetCountry(id uint) (**models.Country, error)
	CreateCountry(country **models.Country) error
	UpdateCountry(country **models.Country) error
	DeleteCountry(id uint) error
}

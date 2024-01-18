/*
 *
 * ProductPage actions
 *
 */

import {
  CREATE_PRODUCT,
  CREATE_PRODUCT_SUCCESS,
  CREATE_PRODUCT_FAIL,
  CREATE_PRODUCT_CLEAR,
  LISTE_PRODUCT,
  LISTE_PRODUCT_FAIL,
  LISTE_PRODUCT_SUCCESS,
  UPDATE_PRODUCT,
  UPDATE_PRODUCT_FAIL,
  UPDATE_PRODUCT_SUCCESS,
  UPLOAD_BANNER,
  UPLOAD_BANNER_FAIL,
  UPLOAD_BANNER_SUCCESS,
  GET_SUBCATEGORY,
  GET_SUBCATEGORY_SUCCESS,
  GET_SUBCATEGORY_FAIL,
  DELETED_PRODUCT,
  DELETED_PRODUCT_SUCCESS,
  DELETED_PRODUCT_FAIL,
} from './constants';

export function listProductAction(params) {
  return {
    type: LISTE_PRODUCT,
    params,
  };
}
export function listProductaSuccessAction(data) {
  return {
    type: LISTE_PRODUCT_SUCCESS,
    data,
  };
}
export function listProductFailAction(error) {
  return {
    type: LISTE_PRODUCT_FAIL,
    error,
  };
}

export function updateProductAction(params) {
  return {
    type: UPDATE_PRODUCT,
    params,
  };
}
export function updateProductaSuccessAction(data) {
  return {
    type: UPDATE_PRODUCT_SUCCESS,
    data,
  };
}
export function updateProductFailAction(error) {
  return {
    type: UPDATE_PRODUCT_FAIL,
    error,
  };
}

export function deletedProductAction(params) {
  return {
    type: DELETED_PRODUCT,
    params,
  };
}
export function deletedProductaSuccessAction(data) {
  return {
    type: DELETED_PRODUCT_SUCCESS,
    data,
  };
}
export function deletedProductFailAction(error) {
  return {
    type: DELETED_PRODUCT_FAIL,
    error,
  };
}

export function createProductAction(params) {
  return {
    type: CREATE_PRODUCT,
    params,
  };
}
export function createProductaSuccessAction(data) {
  return {
    type: CREATE_PRODUCT_SUCCESS,
    data,
  };
}
export function createProductFailAction(error) {
  return {
    type: CREATE_PRODUCT_FAIL,
    error,
  };
}
export function createProductClearAction() {
  return {
    type: CREATE_PRODUCT_CLEAR,
  };
}

// #region uploadBanner
export function uploadBannerAction(file) {
  return {
    type: UPLOAD_BANNER,
    file,
  };
}
export function uploadBannerSuccessAction(data) {
  return {
    type: UPLOAD_BANNER_SUCCESS,
    data,
  };
}
export function uploadBannerFailAction(error) {
  return {
    type: UPLOAD_BANNER_FAIL,
    error,
  };
}
// #endregion uploadBanner

export function getSubCategoryAction() {
  return {
    type: GET_SUBCATEGORY,
  };
}
export function getSubCategorySuccessAction(data) {
  return {
    type: GET_SUBCATEGORY_SUCCESS,
    data,
  };
}
export function getSubCategoryFailAction(error) {
  return {
    type: GET_SUBCATEGORY_FAIL,
    error,
  };
}

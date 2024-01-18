/*
 *
 * ProductPage reducer
 *
 */
import {produce} from 'immer';
import {
  CREATE_PRODUCT,
  CREATE_PRODUCT_FAIL,
  CREATE_PRODUCT_SUCCESS,
  CREATE_PRODUCT_CLEAR,
  GET_SUBCATEGORY,
  GET_SUBCATEGORY_FAIL,
  GET_SUBCATEGORY_SUCCESS,
  LISTE_PRODUCT,
  LISTE_PRODUCT_FAIL,
  LISTE_PRODUCT_SUCCESS,
  UPDATE_PRODUCT,
  UPDATE_PRODUCT_FAIL,
  UPDATE_PRODUCT_SUCCESS,
  UPLOAD_BANNER,
  UPLOAD_BANNER_FAIL,
  UPLOAD_BANNER_SUCCESS,
  DELETED_PRODUCT,
  DELETED_PRODUCT_SUCCESS,
  DELETED_PRODUCT_FAIL,
} from './constants';

export const initialState = {
  products: {
    params: null,
    data: [],
    error: null,
  },
  showproducts: {
    error: null,
    data: [],
  },
  updateproduct: {
    params: null,
    data: null,
    error: null,
  },
  deletedproduct: {
    params: null,
    data: null,
    error: null,
  },
  createproduct: {
    params: null,
    data: [],
    error: null,
  },
  bannerUpload: {
    file: null,
    error: null,
    data: null,
  },
  subcategorys: {
    data: [],
    error: null,
  },
};

/* eslint-disable default-case, no-param-reassign */
const productPageReducer = (state = initialState, action) =>
  produce(state, draft => {
    switch (action.type) {
      case LISTE_PRODUCT:
        draft.products.params = action.params;
        draft.products.error = null;
        break;
      case LISTE_PRODUCT_SUCCESS:
        draft.products.data = action.data;
        draft.products.params = null;
        draft.products.error = null;
        break;
      case LISTE_PRODUCT_FAIL:
        draft.products.params = null;
        draft.products.error = action.error;
        draft.products.data = null;
        break;

      case UPDATE_PRODUCT:
        draft.updateproduct.params = action.params;
        draft.updateproduct.error = null;
        break;
      case UPDATE_PRODUCT_SUCCESS:
        draft.updateproduct.data = action.data;
        draft.updateproduct.params = null;
        draft.updateproduct.error = null;
        break;
      case UPDATE_PRODUCT_FAIL:
        draft.updateproduct.params = null;
        draft.updateproduct.error = action.error;
        draft.updateproduct.data = null;
        break;

      case DELETED_PRODUCT:
        draft.deletedproduct.params = action.params;
        draft.deletedproduct.error = null;
        break;
      case DELETED_PRODUCT_SUCCESS:
        draft.deletedproduct.data = action.data;
        draft.deletedproduct.params = null;
        draft.deletedproduct.error = null;
        break;
      case DELETED_PRODUCT_FAIL:
        draft.deletedproduct.params = null;
        draft.deletedproduct.error = action.error;
        draft.deletedproduct.data = null;
        break;

      case CREATE_PRODUCT:
        draft.createproduct.params = action.params;
        draft.createproduct.error = null;
        draft.createproduct.data = null;
        break;
      case CREATE_PRODUCT_SUCCESS:
        draft.createproduct.data = action.data;
        draft.createproduct.params = null;
        draft.createproduct.error = null;
        break;
      case CREATE_PRODUCT_FAIL:
        draft.createproduct.params = null;
        draft.createproduct.error = action.error;
        draft.createproduct.data = null;
        break;
      case CREATE_PRODUCT_CLEAR:
        draft.createproduct = initialState.createproduct;
        draft.bannerUpload = initialState.bannerUpload;

        break;
      case UPLOAD_BANNER:
        draft.bannerUpload.file = action.file;
        draft.bannerUpload.error = null;
        draft.bannerUpload.data = null;
        break;
      case UPLOAD_BANNER_SUCCESS:
        draft.bannerUpload.file = null;
        draft.bannerUpload.error = null;
        draft.bannerUpload.data = action.data;
        break;
      case UPLOAD_BANNER_FAIL:
        draft.bannerUpload.file = null;
        draft.bannerUpload.error = action.error;
        break;
      case GET_SUBCATEGORY:
        draft.subcategorys.error = null;
        break;
      case GET_SUBCATEGORY_SUCCESS:
        draft.subcategorys.data = action.data;
        break;
      case GET_SUBCATEGORY_FAIL:
        draft.subcategorys.error = action.error;
        break;
    }
  });

export default productPageReducer;

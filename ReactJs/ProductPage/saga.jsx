// import { take, call, put, select } from 'redux-saga/effects';

import { call, put, takeLatest } from 'redux-saga/effects';
import { getSubCategoryAdminApi } from '../../api/category';
import {
  createProductApi,
  getProductApi,
  updateProductApi,
  uploadBannerApi,
  deletedProductApi,
} from '../../api/products';
import {
  NOTIFICATION_TYPE_ERROR,
  NOTIFICATION_TYPE_INFO,
  NOTIFICATION_TYPE_SUCCESS,
} from '../../components/NotificationBox';
import { notificationShowAction } from '../App/actions';

import {
  createProductaSuccessAction,
  createProductFailAction,
  listProductaSuccessAction,
  listProductFailAction,
  updateProductaSuccessAction,
  updateProductFailAction,
  uploadBannerFailAction,
  uploadBannerSuccessAction,
  getSubCategoryFailAction,
  getSubCategorySuccessAction,
  deletedProductaSuccessAction,
  deletedProductFailAction,
} from './actions';
import {
  CREATE_PRODUCT,
  DELETED_PRODUCT,
  GET_SUBCATEGORY,
  LISTE_PRODUCT,
  UPDATE_PRODUCT,
  UPLOAD_BANNER,
} from './constants';

function* getListeProduct({ params }) {
  const response = yield call(getProductApi, params);
  try {
    yield put(listProductaSuccessAction(response.data));
  } catch (error) {
    yield put(listProductFailAction(error.response));
  }
}

export function* updateProduct({ params }) {
  try {
    const response = yield call(updateProductApi, params);
    yield put(updateProductaSuccessAction(response.data));
    yield put(
      notificationShowAction(response.data.message, NOTIFICATION_TYPE_SUCCESS),
    );
  } catch ({ error, response }) {
    yield put(
      notificationShowAction(response.data.message, NOTIFICATION_TYPE_INFO),
    );
    yield put(
      notificationShowAction(
        JSON.stringify(response.data.errors),
        NOTIFICATION_TYPE_ERROR,
      ),
    );

    yield put(updateProductFailAction(error));
  }
}

export function* deletedProduct({ params }) {
  try {
    const response = yield call(deletedProductApi, params);
    yield put(deletedProductaSuccessAction(response.data));
    yield put(
      notificationShowAction(response.data.message, NOTIFICATION_TYPE_SUCCESS),
    );
  } catch ({ error, response }) {
    yield put(
      notificationShowAction(response.data.message, NOTIFICATION_TYPE_INFO),
    );
    yield put(
      notificationShowAction(
        JSON.stringify(response.data.errors),
        NOTIFICATION_TYPE_ERROR,
      ),
    );

    yield put(deletedProductFailAction(error.response));
  }
}

export function* createProduct({ params }) {
  try {
    const response = yield call(createProductApi, params);
    yield put(createProductaSuccessAction(response.data));

    yield put(
      notificationShowAction(
        'به روز رسانی محصولات با موفقیت انجام شد',
        NOTIFICATION_TYPE_SUCCESS,
      ),
    );
  } catch (error) {
    yield put(
      notificationShowAction(
        'به روز رسانی اطلاعات کاربری با خطا مواجه شد',
        NOTIFICATION_TYPE_ERROR,
      ),
    );
    yield put(createProductFailAction(error.response));
  }
}

function* uploadBanner({ file }) {
  try {
    const response = yield call(uploadBannerApi, file);
    yield put(uploadBannerSuccessAction(response.data));
  } catch (error) {
    yield put(uploadBannerFailAction(error.response));
  }
}
// Individual exports for testing

function* getSubCategorysSaga() {
  try {
    const response = yield call(getSubCategoryAdminApi);
    yield put(getSubCategorySuccessAction(response.data));
  } catch (error) {
    yield put(getSubCategoryFailAction(error.response));
  }
}
export default function* productPageSaga() {
  yield takeLatest(LISTE_PRODUCT, getListeProduct);
  yield takeLatest(UPDATE_PRODUCT, updateProduct);
  yield takeLatest(UPLOAD_BANNER, uploadBanner);
  yield takeLatest(DELETED_PRODUCT, deletedProduct);
  yield takeLatest(CREATE_PRODUCT, createProduct);
  yield takeLatest(GET_SUBCATEGORY, getSubCategorysSaga);
}

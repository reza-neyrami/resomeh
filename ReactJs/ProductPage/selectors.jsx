import { createSelector } from 'reselect';
import { initialState } from './reducer';

/**
 * Direct selector to the productPage state domain
 */

const selectProductPageDomain = state => state.productPage || initialState;

/**
 * Other specific selectors
 */

/**
 * Default selector used by ProductPage
 */

export const makeSelectListeProduct = () =>
  createSelector(
    selectProductPageDomain,
    substate => substate.products,
  );
export const makeSelectUpdateProduct = () =>
  createSelector(
    selectProductPageDomain,
    substate => substate.updateproduct,
  );
export const makeSelectCreateProduct = () =>
  createSelector(
    selectProductPageDomain,
    substate => substate.createproduct,
  );

export const makeSelectBannerUpload = () =>
  createSelector(
    selectProductPageDomain,
    appState => appState.bannerUpload,
  );

export const makeSelectSubCategorys = () =>
  createSelector(
    selectProductPageDomain,
    substate => substate.subcategorys.data,
  );
export { selectProductPageDomain };

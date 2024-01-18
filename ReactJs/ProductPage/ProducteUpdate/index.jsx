/**
 *
 * ProducteUpdate
 *
 */

import {
  Avatar,
  Button,
  Card,
  CardActions,
  CardContent,
  CardHeader,
  Grid,
  TextField,
} from '@mui/material';
import PropTypes from 'prop-types';
import  { memo, useCallback, useState } from 'react';
import { connect } from 'react-redux';
import { compose } from 'redux';
import { createStructuredSelector } from 'reselect';
import CountingTextArea from '../../../components/CountingTextArea';
import ErrorMessage from '../../../components/ErrorMessage';
import ReloaderMessage from '../../../components/ReloaderMessage';
import { updateProductAction } from '../actions';
import { makeSelectBannerUpload, makeSelectUpdateProduct } from '../selectors';
import { ProducteUpStyle } from '../styles';

function ProducteUpdate({
  product,
  onSelectBanner,
  dispatch,
  uploadbanner,
  onClose,
  updateproducts,
}) {
  const [productData, setProductData] = useState(product);

  const isUpdated =
    product.name !== productData.name ||
    product.type !== productData.type ||
    product.price !== productData.price ||
    product.code !== productData.code ||
    product.banner !== productData.banner ||
    product.desc !== productData.desc;

  const loading = !!(updateproducts.params || uploadbanner.file);

  const handleSetUpdate = (key, value) => {
    if (product.id) {
      setProductData({ ...productData, [key]: value });
    }
  };

  const handleChangeBanner = e => {
    if ((e.target.files && e.target.files[0]) || null) {
      const file = e.target.files[0];
      onSelectBanner(file);
    }
    const key = e.target.name;
    handleSetBanner(key);
  };

  const handleSetBanner = useCallback(key => {
    if (uploadbanner && uploadbanner.data && !productData.banner) {
      setProductData({ ...productData, [key]: uploadbanner.data.banner });
    }
  }, []);

  function handleNewComment(value) {
    setProductData({ ...productData, desc: value });
  }

  const handleUpdateData = () => {
    dispatch(updateProductAction(productData));
  };

  const handleDeleteData = () => {};

  return (
    <ProducteUpStyle open>
      <Card elevation={3}>
        <CardContent>
          <CardHeader>
            {updateproducts.error && (
              <ErrorMessage
                error={updateproducts.error}
                closeable={false}
                forceMessage={
                  <ReloaderMessage
                    message="در دریافت اطلاعات خطایی به وجود آمده است"
                    reloadMessage="بارگذاری مجدد"
                  />
                }
              />
            )}
          </CardHeader>

          <CardActions>
            <Grid item xs={12}>
              <TextField
                label="نام محصول"
                name="name"
                id="name"
                // disabled={loading}
                value={productData.name || ''}
                onChange={e => handleSetUpdate('name', e.target.value)}
              />
              <TextField
                label="قیمت محصول"
                name="price"
                id="price"
                disabled={loading}
                value={productData.price || ''}
                onChange={e => handleSetUpdate('price', e.target.value)}
              />
            </Grid>
            <Grid item xs={12}>
              <TextField
                label="نوع محصول"
                name="type"
                id="type"
                disabled={loading}
                value={productData.type || ''}
                onChange={e => handleSetUpdate('type', e.target.value)}
              />
              <TextField
                label="کد انحصاری"
                name="code"
                id="code"
                disabled={loading}
                value={productData.code || ''}
                onChange={e => handleSetUpdate('code', e.target.value)}
              />
            </Grid>
          </CardActions>
          <CardActions>
            <Grid item xs={12}>
              <CountingTextArea
                className="Prodct-textArea"
                defaultValue={product.desc || ''}
                maxLength={200}
                placeholder="نظر خود را وارد نمایید"
                cancelable={false}
                value={productData.desc || ''}
                onChange={handleNewComment}
              />
            </Grid>
          </CardActions>
          <CardActions>
            {!uploadbanner.error && productData.banner ? (
              <Avatar src={productData.banner || ''} alt="تصاویر" />
            ) : (
              <CardActions>
                <input
                  accept="image/*"
                  className="images-input"
                  id="contained-button-file"
                  multiple
                  type="file"
                  name="banner"
                  disabled={loading}
                  onChange={handleChangeBanner}
                />
                <label htmlFor="contained-button-file">
                  <Button variant="contained" color="primary" component="span">
                    Upload
                  </Button>
                </label>
              </CardActions>
            )}
          </CardActions>
          <CardActions>
            <Button onClick={handleUpdateData} disabled={!isUpdated || loading}>
              ویرایش
            </Button>
            <Button onClick={handleDeleteData}>حذف</Button>
            <Button onClick={onClose}>بستن</Button>
          </CardActions>
        </CardContent>
      </Card>
    </ProducteUpStyle>
  );
}

ProducteUpdate.propTypes = {
  product: PropTypes.object.isRequired,
  uploadbanner: PropTypes.object.isRequired,
  updateproducts: PropTypes.object.isRequired,
  onSelectBanner: PropTypes.func.isRequired,
  dispatch: PropTypes.func.isRequired,
  onClose: PropTypes.func.isRequired,
};
const mapStateToProps = createStructuredSelector({
  updateproducts: makeSelectUpdateProduct(),
  uploadbanner: makeSelectBannerUpload(),
});

const mapDispatchToProps = dispatch => ({
  dispatch,
});

const withConnect = connect(
  mapStateToProps,
  mapDispatchToProps,
);
export default compose(
  withConnect,
  memo,
)(ProducteUpdate);

/**
 *
 * ProducteCreate
 *
 */

import {
  AddPhotoAlternate,
  AddShoppingCart,
  EditAttributes,
} from "@mui/icons-material";
import {
  Box,
  Card,
  CardActions,
  CardContent,
  CardHeader,
  Fab,
  FormControl,
  Grid,
  InputAdornment,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Tooltip,
} from "@mui/material";

import PropTypes from "prop-types";
import { memo, useEffect, useState } from "react";
import { connect } from "react-redux";
import { compose } from "redux";
import { createStructuredSelector } from "reselect";
import CountingTextArea from "../../../components/CountingTextArea";
import ErrorMessage from "../../../components/ErrorMessage";
import NotificationBox from "../../../components/NotificationBox";
import TagSelectBox from "../../../components/TagSelectBox";
import { BASEURL } from "../../../constanse/constance";
import { getTagsAction } from "../../App/actions";
import { makeSelectCategories } from "../../App/selectors";
import { createProductAction, createProductClearAction } from "../actions";
import {
  makeSelectBannerUpload,
  makeSelectCreateProduct,
  makeSelectSubCategorys,
} from "../selectors";

import { ProductecreateStyle, ProductFiledStyles } from "../styles";
import CategoryName from "./CategoryName";
import FileUploadProgress from "./FileUploadProgress";
import SubName from "./SubName";

function ProducteCreate({
  uploadbanner,
  dispatch,
  onSelectBanner,
  sendDataProduct,
  getTagsFromServer,
  createproduct,
  subnames,
  clearCreateProducts,
}) {
  const [subCategorys, setSubCategorys] = useState();
  const [productData, setProductData] = useState({
    name: "",
    price: 0,
    code: "",
    desc: "",
    type: "",
    banner: null,
    send: "",
    tags: [],
    category: [],
    subcategory: "",
  });
  const [sends] = useState({
    posts: "posts",
    downloads: "downloads",
  });

  const [categories, setCategories] = useState(null);
  function handleSelectBannerImage(file) {
    onSelectBanner(file);
  }

  const handleSetCreate = (key, value) => {
    setProductData({ ...productData, [key]: value });
  };

  function handleNewComment(value) {
    setProductData({ ...productData, desc: value });
  }

  const handleCreateData = () => {
    dispatch(sendDataProduct(productData));
  };
  const handleSetSelectedCategory = (category) => {
    setCategories(category);
  };

  useEffect(() => {
    if (uploadbanner && uploadbanner.data && !productData.banner) {
      setProductData({ ...productData, banner: uploadbanner.data.banner });
    }
    clearCreateProducts;
  }, [uploadbanner.data, productData]);

  useEffect(() => {
    getTagsFromServer();
  }, []);
  return (
    <ProductecreateStyle>
      <Card>
        <CardContent>
          <CardHeader>
            <h1>man Headeram</h1>
          </CardHeader>
          <CardActions>
            <Grid item xs={12}>
              <CountingTextArea
                className="Prodct-textArea"
                defaultValue={productData.desc || ""}
                maxLength={2500}
                placeholder="متن خود را وارد کنید و سپس تایید کنید"
                cancelable={false}
                value={productData.desc || ""}
                onChange={handleNewComment}
              />
            </Grid>
          </CardActions>
          <ProductFiledStyles>
            <TextField
              required
              variant="outlined"
              label="نام محصول"
              name="name"
              id="name"
              value={productData.name || ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <EditAttributes />
                  </InputAdornment>
                ),
              }}
              onChange={(e) => handleSetCreate("name", e.target.value)}
            />

            <TextField
              required
              variant="outlined"
              label="قیمت محصول"
              name="price"
              id="price"
              value={productData.price || ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <EditAttributes />
                  </InputAdornment>
                ),
              }}
              onChange={(e) => handleSetCreate("price", e.target.value)}
            />
          </ProductFiledStyles>
          <ProductFiledStyles>
            <TextField
              required
              className="tag"
              variant="outlined"
              label="نوع محصول"
              name="type"
              id="type"
              value={productData.type || ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <EditAttributes />
                  </InputAdornment>
                ),
              }}
              onChange={(e) => handleSetCreate("type", e.target.value)}
            />

            <TextField
              required
              variant="outlined"
              label="کد انحصاری"
              name="code"
              id="code"
              value={productData.code || ""}
              InputProps={{
                startAdornment: (
                  <InputAdornment position="start">
                    <EditAttributes />
                  </InputAdornment>
                ),
              }}
              onChange={(e) => handleSetCreate("code", e.target.value)}
            />
          </ProductFiledStyles>
          <ProductFiledStyles>
            <Grid item xs={6}>
              گروه دسته بندی
              <CardActions>
                <SubName
                  defaultValue={productData.subcategory || ""}
                  handleClikedCategory={handleSetSelectedCategory}
                  onChange={(defaultValue) =>
                    handleSetCreate("subcategory", defaultValue)
                  }
                />
              </CardActions>
            </Grid>
            <Grid item xs={6}>
              دسته بندی محصولات
              <CardActions>
                {categories && (
                  <CategoryName
                    dataItem={categories}
                    defaultValue={productData.category || ""}
                    subcategory={productData.subcategory || ""}
                    onChange={(defaultValue) =>
                      handleSetCreate("category", defaultValue)
                    }
                  />
                )}
              </CardActions>
            </Grid>
          </ProductFiledStyles>
          <CardHeader>
            <p>
              <h4> افزودن تگ محصولات</h4>
            </p>
          </CardHeader>

          <ProductFiledStyles>
            <TagSelectBox
              label="افزودن  تگ "
              max={5}
              value={productData.tags || ""}
              onChange={(value) => handleSetCreate("tags", value)}
            />
            <Box sx={{ minWidth: 120 }}>
              <FormControl className="form-controls">
                <InputLabel id="demo-simple-select-label">Age</InputLabel>
                <Select
                  labelId="demo-simple-select-label"
                  id="demo-simple-select"
                  value={productData.send}
                  label="ارایه محصول"
                  onChange={(e) => handleSetCreate("send", e.target.value)}
                >
                  <MenuItem value="">
                    <em>None</em>
                  </MenuItem>
                  <MenuItem value={sends.downloads}>downloads</MenuItem>
                  <MenuItem value={sends.posts}>posts</MenuItem>
                </Select>
              </FormControl>
            </Box>
          </ProductFiledStyles>

          <CardActions className="image-class">
            <FileUploadProgress
              banner={
                productData.banner ? `${BASEURL}${productData.banner}` : null
              }
              onSelectBanner={handleSelectBannerImage}
            />
            <AddPhotoAlternate />
          </CardActions>
          <CardActions>
            <Tooltip title="Add" aria-label="add">
              <Fab
                color="secondary"
                className="fab-secen"
                disabled={!productData && productData === null}
                onClick={handleCreateData}
              >
                <AddShoppingCart />
              </Fab>
            </Tooltip>
          </CardActions>
        </CardContent>
      </Card>
      {createproduct.error && (
        <ErrorMessage error={createproduct.error} options={{ 404: "error" }} />
      )}
      <NotificationBox key={createproduct.error} />
    </ProductecreateStyle>
  );
}

ProducteCreate.propTypes = {
  uploadbanner: PropTypes.object.isRequired,
  onSelectBanner: PropTypes.func.isRequired,
  createproduct: PropTypes.object.isRequired,

  subnames: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
  data: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
  dispatch: PropTypes.func.isRequired,
  clearCreateProducts: PropTypes.func.isRequired,
  sendDataProduct: PropTypes.func.isRequired,
  getTagsFromServer: PropTypes.func.isRequired,
};

const mapStateToProps = createStructuredSelector({
  uploadbanner: makeSelectBannerUpload(),
  createproduct: makeSelectCreateProduct(),
  subnames: makeSelectSubCategorys(),
  data: makeSelectCategories(),
});

const mapDispatchToProps = (dispatch) => ({
  dispatch,
  sendDataProduct: (data) => createProductAction(data),
  getTagsFromServer: () => dispatch(getTagsAction()),
  clearCreateProducts: () => dispatch(createProductClearAction()),
});

const withConnect = connect(mapStateToProps, mapDispatchToProps);
export default compose(withConnect, memo)(ProducteCreate);

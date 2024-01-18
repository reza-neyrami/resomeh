/**
 *
 * ProductPage
 *
 */

import { Button, Grid, Paper, Tab, Tabs } from "@mui/material";
import PropTypes from "prop-types";
import { memo, useEffect, useState } from "react";
import { connect } from "react-redux";
import { compose } from "redux";
import { createStructuredSelector } from "reselect";
import ListPaginate from "../../components/ListPaginate";
import DashboardLayout from "../../layouts/DashboardLayout";
import ProducteCreate from "./ProducteCreate";
import {
  deletedProductAction,
  listProductAction,
  updateProductAction,
  uploadBannerAction,
} from "./actions";
import ProductListColumn from "./coulmn/ProductListColumn";
import { makeSelectListeProduct, makeSelectUpdateProduct } from "./selectors";
import { ProducteWraper } from "./styles";
import NotificationBox from "../../components/NotificationBox";

export function ProductPage({
  products,
  dispatch,
  onSelectBanner,
  handleUpdateData,
  update,
}) {
  const [selectedTabs, setSelectedTabs] = useState(0);
  const [deleteArray, setDeleteArray] = useState([]);
  const [selectedProduct, setSelectedProduct] = useState(null);
  const [page, setPage] = useState(1);
  const [pageSize, setPageSize] = useState(10);

  function handlePageChange(p, s) {
    setPage(p);
    setPageSize(s);
  }
  function handlePageSizeChange(s) {
    setPageSize(s);
  }
  function handleSelectBannerImage(file) {
    onSelectBanner({ file });
  }

  function getProductListe() {
    dispatch(listProductAction({ page, size: pageSize }));
  }

  const handleDeletedProduct = (delevalue) => {

    dispatch(deletedProductAction(delevalue));
  };

  useEffect(() => {
    getProductListe();
  }, [page, pageSize]);

  return (
    <DashboardLayout showSidebar>
      <ProducteWraper xs={12} justify="space-between" container>
        <Grid>
          <Tabs
            component={Paper}
            value={selectedTabs}
            onChange={(e, tabIndex) => {
              setSelectedTabs(tabIndex);
            }}
            indicatorColor="primary"
            textColor="primary"
            className="tabs"
          >
            <Tab label="لیست محصولات" />
            <Tab label="ایجاد محصول" />
          </Tabs>
          <Grid>
            {selectedTabs === 0 && (
              <div>
                {products?.data.data && (
                  <div>
                    <ListPaginate
                      columns={ProductListColumn}
                      data={products?.data.data}
                      page={page}
                      size={pageSize}
                      update={update}
                      totals={products.data.total}
                      onChangePage={handlePageChange}
                      onChangePageSize={handlePageSizeChange}
                      handleClikedProduct={setSelectedProduct}
                      handleDeletedProduct={handleDeletedProduct}
                      handleUpdateData={handleUpdateData}
                    />
                  </div>
                )}
              </div>
            )}
          </Grid>
        </Grid>
        <Grid>
          {selectedTabs === 1 && (
            <ProducteCreate onSelectBanner={handleSelectBannerImage} />
          )}
        </Grid>
      </ProducteWraper>
      <NotificationBox />
    </DashboardLayout>
  );
}

ProductPage.propTypes = {
  update: PropTypes.object,
  products: PropTypes.object,
  dispatch: PropTypes.func.isRequired,
  onSelectBanner: PropTypes.func.isRequired,
  handleUpdateData: PropTypes.func.isRequired,
};

const mapStateToProps = createStructuredSelector({
  products: makeSelectListeProduct(),
  update: makeSelectUpdateProduct(),
});
function mapDispatchToProps(dispatch) {
  return {
    dispatch,
    onSelectBanner: (file) => dispatch(uploadBannerAction(file)),
    handleUpdateData: (params) => dispatch(updateProductAction(params)),
  };
}

const withConnect = connect(mapStateToProps, mapDispatchToProps);

export default compose(withConnect, memo)(ProductPage);

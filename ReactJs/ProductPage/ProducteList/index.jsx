/**
 *
 * ProducteList
 *
 */

import {
  Avatar,
  Grid,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TablePagination,
  TableRow,
} from '@mui/material';
import PropTypes from 'prop-types';
import  { memo } from 'react';
import { ProducteListeStyle } from '../styles';
// import styled from 'styled-components';
const columns = [
  { name: 'id', title: 'واحد' },
  { name: 'name', title: 'نام' },
  {
    name: 'desc',
    title: 'توضیحات',
    cast: v => (v.indexOf('.') ? v.slice(0, v.indexOf('.')) : v.slice(0, 50)),
  },
  { name: 'code', title: 'کد' },
  { name: 'type', title: 'نوع' },
  { name: 'price', title: 'قیمت' },
  {
    name: 'banner',
    title: 'تصویر',
    cast: v => <Avatar src={v} variant="rounded" className="avatar-product" />,
  },
];

function ProducteList({
  handleClikedProduct,
  products,
  page,
  size,
  total,
  onPageChange,
}) {
  function handleChangePage(e, newPage) {
    onPageChange(newPage + 1, size);
  }

  function handleChangeRowsPerPage(e) {
    onPageChange(1, parseInt(e.target.value, 10));
  }
  return (
    <ProducteListeStyle>
      {products && (
        <>
          <Grid>
            <Table>
              <TableHead>
                <TableRow>
                  {columns.map(column => (
                    <TableCell
                      className="padd-inputs"
                      key={column.name}
                      align={column.align}
                      style={{ minWidth: column.minWidth }}
                    >
                      {column.title}
                    </TableCell>
                  ))}
                </TableRow>
              </TableHead>
              <TableBody>
                {products.map(product => (
                  <TableRow
                    role="checkbox"
                    tabIndex={-1}
                    key={product.id}
                    onClick={() => handleClikedProduct(product)}
                  >
                    {columns.map(column => {
                      const value = product[column.name];
                      return (
                        <TableCell
                          className="fild-rows"
                          key={column.name}
                          align={column.align}
                          dir={column.dir}
                        >
                          {column.cast ? column.cast(value) : value}
                        </TableCell>
                      );
                    })}
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </Grid>
          <TablePagination
            rowsPerPageOptions={[10, 25, 50]}
            component="div"
            count={total}
            rowsPerPage={size}
            page={page - 1}
            labelRowsPerPage=""
            labelDisplayedRows={() => `صفحه ${page}`}
            onChangePage={handleChangePage}
            onRowsPerPageChange={handleChangeRowsPerPage}
          />
          <Grid />
        </>
      )}
    </ProducteListeStyle>
  );
}

ProducteList.propTypes = {
  products: PropTypes.arrayOf(PropTypes.object).isRequired,
  page: PropTypes.number,
  size: PropTypes.number,
  total: PropTypes.number,
  onPageChange: PropTypes.func,
  handleClikedProduct: PropTypes.func.isRequired,
};

export default memo(ProducteList);

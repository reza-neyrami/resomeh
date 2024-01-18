import {
  Button,
  Checkbox,
  Dialog,
  DialogActions,
  DialogContent,
  DialogTitle,
  Grid,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TablePagination,
  TableRow,
  TextField,
} from "@mui/material";
import PropTypes from "prop-types";
import { memo, useEffect, useState } from "react";
import ImageUploadComponent from "../../containers/BannerSlice/ImageUploadComponent";
import { CategoryListeStyle } from "../../containers/CategoryPage/styles";
import CountingTextArea from "../CountingTextArea";
import NotificationBox from "./../NotificationBox/index";

const ListPaginate = ({
  handleClikedProduct,
  data,
  page,
  size,
  totals,
  onChangePage,
  columns,
  handleUpdateData,
  handleDeletedProduct,
  handleotherProduct,
  onSelectBanner,
}) => {
  const [selectedRows, setSelectedRows] = useState([]);
  const [editDialogOpen, setEditDialogOpen] = useState(false);
  const [editData, setEditData] = useState(null);

  function handleSelectAll() {
    if (selectedRows.length === data.length) {
      setSelectedRows(data.map((rowData) => rowData.id));
    } else {
      throw new Error(`${rowData.id} مقدار وجود ندارد`);
    }
    handleotherProduct(selectedRows);
  }

  function isSelected(id) {
    return selectedRows.indexOf(id) !== -1;
  }

  function handleChangePage(e, newPage) {
    onChangePage(newPage + 1, size);
  }

  function handleChangeRowsPerPage(e) {
    onChangePage(1, parseInt(e.target.value, 10));
  }

  const handleEditClick = (rowData, column) => {
    setEditData(
      columns.reduce(
        (result, column) => {
          if (column.editable) {
            return { ...result, [column.field]: rowData[column.field] };
          }
          return result;
        },
        { id: rowData.id }
      )
    );
    setEditDialogOpen(true);
  };

  const handleEditDialogClose = () => {
    setEditDialogOpen(false);
  };

  function handleEditSubmit(updatedData) {
    const updatedRow = { ...data.find((row) => row.id === updatedData.id) };
    handleUpdateData(updatedData);
    setEditDialogOpen(false);
  }

  function handleDelete() {
    const newData = data
      .filter((row) => isSelected(row.id))
      .map((item) => item.id);
    handleDeletedProduct(selectedRows);
    // setSelectedRows([]);
  }

  return (
    <CategoryListeStyle>
      {data ? (
        <>
          <Grid container alignItems="center" justifyContent="space-between">
            <Grid item>
              <Button
                variant="outlined"
                color="secondary"
                onClick={handleDelete}
              >
                حذف
              </Button>
            </Grid>
            <Grid item>
              <TablePagination
                rowsPerPageOptions={[10, 25, 50]}
                component="div"
                count={totals || 4}
                rowsPerPage={size}
                page={page - 1}
                onPageChange={handleChangePage}
                onRowsPerPageChange={handleChangeRowsPerPage}
              />
            </Grid>
          </Grid>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell padding="checkbox">
                  <Checkbox
                    onChange={handleSelectAll}
                    indeterminate={
                      selectedRows.length > 0 &&
                      selectedRows.length < data.length
                    }
                    checked={selectedRows.length === data.length}
                  />
                </TableCell>
                {columns.map((column) => (
                  <TableCell
                    className={column.clasess}
                    key={column?.name}
                    align={column?.align}
                    style={{ minWidth: column?.minWidth }}
                  >
                    {column?.title}
                  </TableCell>
                ))}
              </TableRow>
            </TableHead>
            <TableBody>
              {data &&
                data?.map((rowData) => (
                  <TableRow key={rowData.id} role="checkbox" tabIndex={-1}>
                    <TableCell padding="checkbox">
                      <Checkbox
                        checked={isSelected(rowData.id)}
                        onChange={(e) =>
                          setSelectedRows((prev) =>
                            e.target.checked
                              ? [...prev, rowData.id]
                              : prev.filter((id) => id !== rowData.id)
                          )
                        }
                      />
                    </TableCell>
                    {columns.map((column) => (
                      <TableCell
                        className={column.clasess}
                        key={`${rowData.id}-${column?.name}`}
                        align={column?.align}
                        onClick={() => handleEditClick(rowData, column)}
                      >
                        {column.cast
                          ? column.cast(rowData[column.field])
                          : rowData[column.field]}
                      </TableCell>
                    ))}
                  </TableRow>
                ))}
            </TableBody>
          </Table>
          {editDialogOpen && (
            <Dialog
              open
              onClose={handleEditDialogClose}
              aria-labelledby="form-dialog-title"
            >
              <DialogTitle id="form-dialog-title">Edit Data</DialogTitle>
              <DialogContent>
                {editData && (
                  <>
                    {columns?.map((column) => (
                      <div key={column.field}>
                        {column?.editable === true ? (
                          column.cast && !column.type ? (
                            column.cast(
                              editData[column.field] || "",
                              (newValue) =>
                                setEditData({
                                  ...editData,
                                  [column.field]: newValue,
                                })
                            )
                          ) : column.type === "text" ? (
                            <CountingTextArea
                              margin="dense"
                              maxLength={4000}
                              label={column.title}
                              type="text"
                              defaultValue={editData[column?.field]}
                              onChange={(e) =>
                                setEditData({
                                  ...editData,
                                  [column.field]: e.target.value,
                                })
                              }
                            />
                          ) : column.type === "image" ? (
                            <ImageUploadComponent
                              label={column.title}
                              onSelectBanner={(newValue) =>
                                setEditData({
                                  ...editData,
                                  [column.field]: newValue,
                                })
                              }
                            />
                          ) : (
                            <TextField
                              margin="dense"
                              label={column.title}
                              type="text"
                              fullWidth
                              value={editData[column?.field]}
                              onChange={(e) =>
                                setEditData({
                                  ...editData,
                                  [column.field]: e.target.value,
                                })
                              }
                            />
                          )
                        ) : (
                          editData[column.field]
                        )}
                      </div>
                    ))}
                  </>
                )}
              </DialogContent>
              <DialogActions>
                <Button onClick={handleEditDialogClose} color="primary">
                  Cancel
                </Button>
                <Button
                  onClick={() => handleEditSubmit(editData)}
                  color="primary"
                >
                  Save
                </Button>
              </DialogActions>
            </Dialog>
          )}
        </>
      ) : (
        <h1>Loading...</h1>
      )}
      <NotificationBox />
    </CategoryListeStyle>
  );
};

ListPaginate.propTypes = {
  handleClikedProduct: PropTypes.func.isRequired,
  handleUpdateData: PropTypes.func,
  handleotherProduct: PropTypes.func,
  onSelectBanner: PropTypes.func,
  handleDeletedProduct: PropTypes.func,
  data: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
  page: PropTypes.number.isRequired,
  size: PropTypes.number.isRequired,
  totals: PropTypes.any,
  onChangePage: PropTypes.func.isRequired,
  columns: PropTypes.arrayOf(
    PropTypes.shape({
      name: PropTypes.string.isRequired,
      title: PropTypes.string.isRequired,
      align: PropTypes.oneOf(["left", "center", "right"]),
      minWidth: PropTypes.number,
      cast: PropTypes.func,
      field: PropTypes.string,
    })
  ).isRequired,
};
export default memo(ListPaginate);

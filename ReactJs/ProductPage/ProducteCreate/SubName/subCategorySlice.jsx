import { createSlice } from "@reduxjs/toolkit";
import { getSubCategoryAdminApi } from "../../../../api/category";
const initialState = {
   
    data: [],
    loading: false,
    error: null,

};


export const subCategorySlice = createSlice({
  name: "subCategory",
  initialState,
  reducers: {
    getSubCategoriesRequest: (state) => {
      state.loading = true;
    },
    getSubCategoriesSuccess: (state, action) => {
      state.data = action.payload;
      state.loading = false;
      state.error = null;
    },
    getSubCategoriesFailure: (state, action) => {
      state.loading = false;
      state.error = action.payload;
    },
  },
});

export const {
  getSubCategoriesRequest,
  getSubCategoriesSuccess,
  getSubCategoriesFailure,
} = subCategorySlice.actions;

export const fetchSubCategories = () => async (dispatch) => {
  dispatch(getSubCategoriesRequest());
  try {
    const response = await  getSubCategoryAdminApi();
    dispatch(getSubCategoriesSuccess(response.data));
  } catch (error) {
    dispatch(getSubCategoriesFailure(error.message));
  }
};



export const selectSubCategories = (state) => state.subCategory.data;
export const selectLoading = (state) => state.subCategory.loading;
export const selectError = (state) => state.subCategory.error;

export default subCategorySlice.reducer;

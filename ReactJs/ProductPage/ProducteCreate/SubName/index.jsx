import {
  FormControl,
  Input,
  InputLabel,
  MenuItem,
  Select,
} from "@mui/material";
import PropTypes from "prop-types";
import { memo, useCallback, useEffect, useState } from "react";
import { connect, useDispatch, useSelector } from "react-redux";
import { compose } from "redux";
import { createStructuredSelector } from "reselect";
import LoadingWithText from "../../../../components/LoadingWithText";
import { SubNameStyles } from "../styles";
import { fetchSubCategories, selectSubCategories } from "./subCategorySlice";
const ITEM_HEIGHT = 48;
const ITEM_PADDING_TOP = 8;
const MenuProps = {
  PaperProps: {
    style: {
      maxHeight: ITEM_HEIGHT * 4.5 + ITEM_PADDING_TOP,
      width: 250,
    },
  },
};
function SubName({ onChange, handleClikedCategory, defaultValue }) {
  const [items, setItems] = useState("");

  const dataItem = useSelector(selectSubCategories);
  const dispatch = useDispatch();
  const handleSetChange = useCallback((val) => {
    if (val) {
      // onChange({ title: val.title, id: val.id });
      onChange(val);
      setItems(val);
    }
  }, []);

  useEffect(() => {
    dispatch(fetchSubCategories());
  }, []);

  return (
    <SubNameStyles>
      {dataItem?.data ? (
        <FormControl className="form">
          <InputLabel id="subName" className="sub-form">
            گروه کتگوری
          </InputLabel>
          <Select
            value={items}
            id="subName"
            onChange={(e) => handleSetChange(e.target.value)}
            input={<Input id="sub-nameId" />}
            MenuProps={MenuProps}
          >
            {dataItem.data ? (
              Object.values(dataItem.data).map((name) => (
                <MenuItem
                  key={name?.id}
                  value={name?.id}
                  onClick={() => handleClikedCategory(name?.category)}
                >
                  {name?.title}
                </MenuItem>
              ))
            ) : (
              <div className="loading-data">
                <LoadingWithText />
              </div>
            )}
          </Select>
        </FormControl>
      ) : (
        <LoadingWithText />
      )}
    </SubNameStyles>
  );
}

SubName.propTypes = {
  onChange: PropTypes.func.isRequired,
  handleClikedCategory: PropTypes.func,
  // handleChange: PropTypes.func.isRequired,
};

const mapStateToProps = createStructuredSelector({
  // multipale: makeSelectTariffsMultipale(),
});

function mapDispatchToProps(dispatch) {
  return {
    dispatch,
    // getSubCategory: () => dispatch(getSubCategoryAction()),
  };
}

const withConnect = connect(mapStateToProps, mapDispatchToProps);

export default compose(withConnect, memo)(SubName);

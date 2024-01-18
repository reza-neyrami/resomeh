import {
  FormControl,
  Input,
  InputLabel,
  MenuItem,
  Select,
} from '@mui/material';
import PropTypes from 'prop-types';
import  { memo, useCallback, useEffect, useMemo, useState } from 'react';
import { connect } from 'react-redux';
import { compose } from 'redux';
import { createStructuredSelector } from 'reselect';
import LoadingWithText from '../../../../components/LoadingWithText';
import { getSubCategoryAction } from '../../actions';
import { SubNameStyles } from '../styles';
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
function CategoryName({ dataItem, onChange }) {
  const [items, setItems] = useState('');
  const handleSetChange = val => {
    if (val) {
      onChange(val);
      setItems(val);
    }
  };

  return (
    <SubNameStyles>
      {dataItem ? (
        <FormControl className="form">
          <InputLabel id="category-name" className="sub-form">
            کتگوری
          </InputLabel>
          <Select
            // defaultValue={defaultValue}
            value={items}
            id="category-name"
            onChange={e => handleSetChange(e.target.value)}
            input={<Input id="sub-category_id" />}
            MenuProps={MenuProps}
          >
            {dataItem ? (
              Object.values(dataItem).map(name => (
                <MenuItem key={name.id} value={name.id}>
                  {name.title}
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

CategoryName.propTypes = {
  dataItem: PropTypes.oneOfType([PropTypes.object, PropTypes.array]),
  onChange: PropTypes.func.isRequired,
  // handleChange: PropTypes.func.isRequired,
};

const mapStateToProps = createStructuredSelector({
  // multipale: makeSelectTariffsMultipale(),
});

function mapDispatchToProps(dispatch) {
  return {
    dispatch,
  };
}

const withConnect = connect(
  mapStateToProps,
  mapDispatchToProps,
);

export default compose(
  withConnect,
  memo,
)(CategoryName);

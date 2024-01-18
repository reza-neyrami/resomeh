import React from "react";
import { BASEURL } from "../../../constanse/constance";
const AvatarCell = ({ value }) => {
  return <img src={`${BASEURL}${value}`} alt="banner" />;
};

export default AvatarCell;

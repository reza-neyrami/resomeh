import { Avatar } from "@mui/material";
import React from "react";
import { BASEURL } from "./../../../constanse/constance";

const ProductListColumn = [
  {
    name: "ID",
    field: "id",
    title: "Number",
  },
  {
    name: "Name",
    field: "name",
    editable: true,
    title: "نام",
    width: 100,
  },
  {
    name: "Desc",
    field: "desc",
    editable: true,
    title: "توضیحات",
    width: 250,
    type: "text",
    clasess: "hidden",
    cast: (v) =>
      v.indexOf(".")
        ? v.slice(0, v.indexOf(",") ?? v.indexOf("."))
        : v.slice(0, 3),
  },
  { name: "Code", field: "code", editable: true, title: "کد" },
  { name: "Type", field: "type", editable: true, title: "نوع" },
  { name: "Price", field: "price", editable: true, title: "قیمت" },
  {
    name: "Banner",
    field: "banner",
    title: "تصویر",
    type: "image",
    width: 250,
    editable: true,
    cast: (value) => (
      <Avatar
        src={`${BASEURL}${value}`}
        variant="rounded"
        className="avatar-product"
      />
    ),
  },
].map((columnscourse) => ({
  ...columnscourse,
  align: columnscourse.align || "left",
}));

export default ProductListColumn;

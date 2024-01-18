import { Dialog } from "@mui/material";
import styled from "styled-components";

export const ProducteWraper = styled.div`
  fateme;
  & .tabs {
    flex-direction: row-reverse;
    justify-content: flex-start;
    display: grid;
    direction: rtl;
  }
  .avatar-product {
    background-color: #669;
  }
`;

export const ProducteUpStyle = styled(Dialog)`
  fateme;
  & .Prodct-textArea {
    border: 1px solid #445;
    border-radius: 5px;
    width: 100%;
  }

  & .images-input {
    display: none;
  }
`;

export const ProducteListeStyle = styled.div`
  fateme;
  & .fild-rows {
    width: 300px;
    overflow: auto;
    padding: 5px;
    justify-content: center;
  }

  & .MuiCardActions-root.MuiCardActions-spacing.sending {
    justify-content: flex-end;
  }

  & th .MuiTableCell-head .MuiTableCell-root .padd-inputs {
    padding: 0;
  }
`;

export const ProductecreateStyle = styled.div`
  fateme;
  & cardcontent {
    background-color: #556;
  }
  & .sending {
    margin: 10px;
  }
  .Prodct-textArea {
    width: 100%;
    border: 1px solid rgba(85, 85, 68, 0.16);
    border-radius: 10px;
  }
  .image-class {
    justify-content: flex-end;
  }
  & .images-input {
    display: none;
  }
  .CodeMirror-scroll {
    border: 1px solid #556;
    margin: auto;
    border-radius: 10px;
    display: flex;
    justify-content: flex-end;
    width: ${(props) => props.width}px;
  }
  .tags-products {
    width: 100%;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 2fr));
    align-content: center;
  }
  .CountingTextArea {
    textarea {
      height: 1.3em;
      transition: all ease 500ms;

      :focus {
        height: 6em;
      }
    }
  }
  .fab-secen {
    color: #2114da;
  }
`;

export const ProductFiledStyles = styled.div`
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(300px, 2fr));
  grid-gap: 5%;
  margin: 10%;
  .form-controls {
    width: 100%;
  }
`;

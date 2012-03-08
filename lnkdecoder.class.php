<?php
/**
 * MSshlnk
 *
 * LICENSE PHP License V3.01
 *
 * @category   filesystem
 * @copyright  Copyright (c)2011 Boris Manojlovic / Ioda-Net Sàrl
 * @license		PHP License v3.01 http://www.php.net/license/3_01.txt
 *
 * @desc		MSshlnk is a class dedicated to analyze and ouput informations about
 * 				Microsoft Shell Link files (.lnk)
 *				It should be able to handle 95,Me,XP,2003 versions and Vista+
 *				Reference and documentation are available in docs subdir
 *
 * @revision   $Rev: $
 * @author 	   Boris Manojlovic, Bruno Friedmann
 * @date	   $Date: $
 * @version    $Id: $
 *
 * @todo 		Have a look into code, there's improvement that can take place
 *
 * */

//@todo Find a NameSpace

class MSshlnk {
  public $lnk_bin = null;
  public $errno = 0;
  public $errstring = "";
  public $DEBUG = false;

  // possible errors
  private $_ERROR = array();

  // all header flags of lnk file
  public $H_FLAG = array();

  // flags in .lnk file
  public $LinkFlags = array();

  public $FileAttributes = array();
  public $LinkTargetIDList = array();
  public $LinkInfo = array();
  public $VolumeIDInfo = array();
  public $StructSize = array();
  public $KnownGUIDS = array();
  private $ItemIDSize = -1;

  public function MSshlnk()
  {
    // class errors...
    $this->_ERROR[1]="Not MS lnk file";
    $this->_ERROR[2]="File does not exist";
    $this->_ERROR[3]="stat failed";
    $this->_ERROR[4]="Corrupted LinkCLSID";
    $this->_ERROR[5]="Requested Structure does not exist";

    // link flags informations
    $this->H_FLAG['HasLinkTargetIDList']=array(1, true);
    $this->H_FLAG['HasLinkInfo']=array(2, true);
    $this->H_FLAG['HasName']=array(4, true);
    $this->H_FLAG['HasRelativePath']=array(8, true);
    $this->H_FLAG['HasWorkingDir']=array(16, true);
    $this->H_FLAG['HasArguments']=array(32, true);
    $this->H_FLAG['HasIconLocation']=array(64, true);
    $this->H_FLAG['IsUnicode']=array(128, false);
    $this->H_FLAG['ForceNoLinkInfo']=array(256, false);
    $this->H_FLAG['HasExpString']=array(512, true);
    $this->H_FLAG['RunInSeparateProcess']=array(1024, false);
    //$this->H_FLAG['Unused1']=2048;
    $this->H_FLAG['HasDarwinID']=array(4096, true);
    $this->H_FLAG['RunAsUser']=array(8192, false);
    $this->H_FLAG['HasExpIcon']=array(16384, true);
    $this->H_FLAG['NoPidlAlias']=array(32768, false);
    //$this->H_FLAG['Unused2']=array(65536;
    $this->H_FLAG['RunWithShimLayer']=array(131072, true);
    $this->H_FLAG['ForceNoLinkTrack']=array(262144, false);
    $this->H_FLAG['EnableTargetMetadata']=array(524288, true);
    $this->H_FLAG['DisableLinkPathTracking']=array(1048576, false);
    $this->H_FLAG['DisableKnownFolderTracking']=array(2097152, false);
    $this->H_FLAG['DisableKnownFolderAlias']=array(4194304, false);
    $this->H_FLAG['AllowLinkToLink']=array(8388608, false);
    $this->H_FLAG['UnaliasOnSave']=array(16777216, false);
    $this->H_FLAG['PreferEnvironmentPath']=array(33554432, false);
    $this->H_FLAG['KeepLocalIDListForUNCTarget']=array(67108864, false);

    // Size of Lnk Header
    $this->StructSize['HeaderSize'] = 76;

    // target file file attributes
    $this->F_FLAG['FILE_ATTRIBUTE_READONLY'] = 1;
    $this->F_FLAG['FILE_ATTRIBUTE_HIDDEN'] = 2;
    $this->F_FLAG['FILE_ATTRIBUTE_SYSTEM'] = 4;
    //$this->F_FLAG['Reserved1'] = 8;
    $this->F_FLAG['FILE_ATTRIBUTE_DIRECTORY'] = 16;
    $this->F_FLAG['FILE_ATTRIBUTE_ARCHIVE'] = 32;
    //$this->F_FLAG['Reserved2'] = 64;
    $this->F_FLAG['FILE_ATTRIBUTE_NORMAL'] = 128;
    $this->F_FLAG['FILE_ATTRIBUTE_TEMPORARY'] = 256;
    $this->F_FLAG['FILE_ATTRIBUTE_SPARSE_FILE'] = 512;
    $this->F_FLAG['FILE_ATTRIBUTE_REPARSE_POINT'] = 1024;
    $this->F_FLAG['FILE_ATTRIBUTE_COMPRESSED'] = 2048;
    $this->F_FLAG['FILE_ATTRIBUTE_OFFLINE'] = 4096;
    $this->F_FLAG['FILE_ATTRIBUTE_NOT_CONTENT_INDEXED'] = 8192;
    $this->F_FLAG['FILE_ATTRIBUTE_ENCRYPTED'] = 16384;

    // window start position - behaviour
    $this->WM_CMD[1] = 'SW_SHOWNORMAL';
    $this->WM_CMD[3] = 'SW_SHOWMAXIMIZED';
    $this->WM_CMD[7] = 'SW_SHOWMINNOACTIVE';

    $this->L_IFLAGS['VolumeIDAndLocalBasePath'] = 1;
    $this->L_IFLAGS['CommonNetworkRelativeLinkAndPathSuffix'] = 2;

    $this->L_INFLAGS['ValidDevice'] = 1;
    $this->L_INFLAGS['ValidNetType'] = 2;

    $this->DRIVE_TYPE[0] = 'DRIVE_UNKNOWN';
    $this->DRIVE_TYPE[1] = 'DRIVE_NO_ROOT_DIR';
    $this->DRIVE_TYPE[2] = 'DRIVE_REMOVABLE';
    $this->DRIVE_TYPE[3] = 'DRIVE_FIXED';
    $this->DRIVE_TYPE[4] = 'DRIVE_REMOTE';
    $this->DRIVE_TYPE[5] = 'DRIVE_CDROM';
    $this->DRIVE_TYPE[6] = 'DRIVE_RAMDISK';

    $this->NET_PROVIDER_TYPE[0x0010000] = 'WNNC_NET_MSNET';
    $this->NET_PROVIDER_TYPE[0x0020000] = 'WNNC_NET_LANMAN';
    $this->NET_PROVIDER_TYPE[0x0030000] = 'WNNC_NET_NETWARE';
    $this->NET_PROVIDER_TYPE[0x0040000] = 'WNNC_NET_VINES';
    $this->NET_PROVIDER_TYPE[0x0050000] = 'WNNC_NET_10NET';
    $this->NET_PROVIDER_TYPE[0x0060000] = 'WNNC_NET_LOCUS';
    $this->NET_PROVIDER_TYPE[0x0070000] = 'WNNC_NET_SUN_PC_NFS';
    $this->NET_PROVIDER_TYPE[0x0080000] = 'WNNC_NET_LANSTEP';
    $this->NET_PROVIDER_TYPE[0x0090000] = 'WNNC_NET_9TILES';
    $this->NET_PROVIDER_TYPE[0x00A0000] = 'WNNC_NET_LANTASTIC';
    $this->NET_PROVIDER_TYPE[0x00B0000] = 'WNNC_NET_AS400';
    $this->NET_PROVIDER_TYPE[0x00C0000] = 'WNNC_NET_FTP_NFS';
    $this->NET_PROVIDER_TYPE[0x00D0000] = 'WNNC_NET_PATHWORKS';
    $this->NET_PROVIDER_TYPE[0x00E0000] = 'WNNC_NET_LIFENET';
    $this->NET_PROVIDER_TYPE[0x00F0000] = 'WNNC_NET_POWERLAN';
    $this->NET_PROVIDER_TYPE[0x00100000] = 'WNNC_NET_BWNFS';
    $this->NET_PROVIDER_TYPE[0x00110000] = 'WNNC_NET_COGENT';
    $this->NET_PROVIDER_TYPE[0x00120000] = 'WNNC_NET_FARALLON';
    $this->NET_PROVIDER_TYPE[0x00130000] = 'WNNC_NET_APPLETALK';
    $this->NET_PROVIDER_TYPE[0x00140000] = 'WNNC_NET_INTERGRAPH';
    $this->NET_PROVIDER_TYPE[0x00150000] = 'WNNC_NET_SYMFONET';
    $this->NET_PROVIDER_TYPE[0x00160000] = 'WNNC_NET_CLEARCASE';
    $this->NET_PROVIDER_TYPE[0x00170000] = 'WNNC_NET_FRONTIER';
    $this->NET_PROVIDER_TYPE[0x00180000] = 'WNNC_NET_BMC';
    $this->NET_PROVIDER_TYPE[0x00190000] = 'WNNC_NET_DCE';
    $this->NET_PROVIDER_TYPE[0x001A0000] = 'WNNC_NET_AVID';
    $this->NET_PROVIDER_TYPE[0x001B0000] = 'WNNC_NET_DOCUSPACE';
    $this->NET_PROVIDER_TYPE[0x001C0000] = 'WNNC_NET_MANGOSOFT';
    $this->NET_PROVIDER_TYPE[0x001D0000] = 'WNNC_NET_SERNET';
    $this->NET_PROVIDER_TYPE[0X001E0000] = 'WNNC_NET_RIVERFRONT1';
    $this->NET_PROVIDER_TYPE[0x001F0000] = 'WNNC_NET_RIVERFRONT2';
    $this->NET_PROVIDER_TYPE[0x00200000] = 'WNNC_NET_DECORB';
    $this->NET_PROVIDER_TYPE[0x00210000] = 'WNNC_NET_PROTSTOR';
    $this->NET_PROVIDER_TYPE[0x00220000] = 'WNNC_NET_FJ_REDIR';
    $this->NET_PROVIDER_TYPE[0x00230000] = 'WNNC_NET_DISTINCT';
    $this->NET_PROVIDER_TYPE[0x00240000] = 'WNNC_NET_TWINS';
    $this->NET_PROVIDER_TYPE[0x00250000] = 'WNNC_NET_RDR2SAMPLE';
    $this->NET_PROVIDER_TYPE[0x00260000] = 'WNNC_NET_CSC';
    $this->NET_PROVIDER_TYPE[0x00270000] = 'WNNC_NET_3IN1';
    $this->NET_PROVIDER_TYPE[0x00290000] = 'WNNC_NET_EXTENDNET';
    $this->NET_PROVIDER_TYPE[0x002A0000] = 'WNNC_NET_STAC';
    $this->NET_PROVIDER_TYPE[0x002B0000] = 'WNNC_NET_FOXBAT';
    $this->NET_PROVIDER_TYPE[0x002C0000] = 'WNNC_NET_YAHOO';
    $this->NET_PROVIDER_TYPE[0x002D0000] = 'WNNC_NET_EXIFS';
    $this->NET_PROVIDER_TYPE[0x002E0000] = 'WNNC_NET_DAV';
    $this->NET_PROVIDER_TYPE[0x002F0000] = 'WNNC_NET_KNOWARE';
    $this->NET_PROVIDER_TYPE[0x00300000] = 'WNNC_NET_OBJECT_DIRE';
    $this->NET_PROVIDER_TYPE[0x00310000] = 'WNNC_NET_MASFAX';
    $this->NET_PROVIDER_TYPE[0x00320000] = 'WNNC_NET_HOB_NFS';
    $this->NET_PROVIDER_TYPE[0x00330000] = 'WNNC_NET_SHIVA';
    $this->NET_PROVIDER_TYPE[0x00340000] = 'WNNC_NET_IBMAL';
    $this->NET_PROVIDER_TYPE[0x00350000] = 'WNNC_NET_LOCK';
    $this->NET_PROVIDER_TYPE[0x00360000] = 'WNNC_NET_TERMSRV';
    $this->NET_PROVIDER_TYPE[0x00370000] = 'WNNC_NET_SRT';
    $this->NET_PROVIDER_TYPE[0x00380000] = 'WNNC_NET_QUINCY';
    $this->NET_PROVIDER_TYPE[0x00390000] = 'WNNC_NET_OPENAFS';
    $this->NET_PROVIDER_TYPE[0X003A0000] = 'WNNC_NET_AVID1';
    $this->NET_PROVIDER_TYPE[0x003B0000] = 'WNNC_NET_DFS';
    $this->NET_PROVIDER_TYPE[0x003C0000] = 'WNNC_NET_KWNP';
    $this->NET_PROVIDER_TYPE[0x003D0000] = 'WNNC_NET_ZENWORKS';
    $this->NET_PROVIDER_TYPE[0x003E0000] = 'WNNC_NET_DRIVEONWEB';
    $this->NET_PROVIDER_TYPE[0x003F0000] = 'WNNC_NET_VMWARE';
    $this->NET_PROVIDER_TYPE[0x00400000] = 'WNNC_NET_RSFX';
    $this->NET_PROVIDER_TYPE[0x00410000] = 'WNNC_NET_MFILES';
    $this->NET_PROVIDER_TYPE[0x00420000] = 'WNNC_NET_MS_NFS';
    $this->NET_PROVIDER_TYPE[0x00430000] = 'WNNC_NET_GOOGLE';

    $this->KnownGUIDS["{208D2C60-3AEA-1069-A2D7-08002B30309D}"] = "CLSID_NetworkPlaces";
    $this->KnownGUIDS["{46E06680-4BF0-11D1-83EE-00A0C90DC849}"] = "CLSID_NetworkDomain";
    $this->KnownGUIDS["{C0542A90-4BF0-11D1-83EE-00A0C90DC849}"] = "CLSID_NetworkServer";
    $this->KnownGUIDS["{54A754C0-4BF1-11D1-83EE-00A0C90DC849}"] = "CLSID_NetworkShare";
    $this->KnownGUIDS["{20D04FE0-3AEA-1069-A2D8-08002B30309D}"] = "CLSID_MyComputer";
    $this->KnownGUIDS["{871C5380-42A0-1069-A2EA-08002B30309D}"] = "CLSID_Internet";
    $this->KnownGUIDS["{F3364BA0-65B9-11CE-A9BA-00AA004AE837}"] = "CLSID_ShellFSFolder";
    $this->KnownGUIDS["{645FF040-5081-101B-9F08-00AA002F954E}"] = "CLSID_RecycleBin";
    $this->KnownGUIDS["{21EC2020-3AEA-1069-A2DD-08002B30309D}"] = "CLSID_ControlPanel";
    $this->KnownGUIDS["{450D8FBA-AD25-11D0-98A8-0800361B1103}"] = "CLSID_MyDocuments";

    // http://source.winehq.org/source/dlls/shell32/pidl.h
    $this->PIDL[0x00] = 'PT_CPLAPPLET';
    $this->PIDL[0x1F] = 'PT_GUID';
    $this->PIDL[0x23] = 'PT_DRIVE';
    $this->PIDL[0x25] = 'PT_DRIVE2';
    $this->PIDL[0x29] = 'PT_DRIVE3';
    $this->PIDL[0x2E] = 'PT_SHELLEXT';
    $this->PIDL[0x2F] = 'PT_DRIVE1';
    $this->PIDL[0x30] = 'PT_FOLDER1';
    $this->PIDL[0x31] = 'PT_FOLDER';
    $this->PIDL[0x32] = 'PT_VALUE';
    $this->PIDL[0x34] = 'PT_VALUEW';
    $this->PIDL[0x35] = 'PT_FOLDERW';
    $this->PIDL[0x41] = 'PT_WORKGRP';
    $this->PIDL[0x42] = 'PT_COMP';
    $this->PIDL[0x46] = 'PT_NETPROVIDER';
    $this->PIDL[0x47] = 'PT_NETWORK';
    $this->PIDL[0x61] = 'PT_IESPECIAL1';
    $this->PIDL[0x70] = 'PT_YAGUID';  // yet another guid
    $this->PIDL[0xb1] = 'PT_IESPECIAL2';
    $this->PIDL[0xc3] = 'PT_SHARE';

    return $this;
  }

  private function _pack_array($format,$arr)
  {
    return call_user_func_array('pack',array_merge(array($format),(array)$arr));
  }

  private function _getvalue($array, $key)
  {
    return $array[$key];
  }


  private function _set_error($val)
  {
    $this->errno = $val;
    $this->errstring = $this->_ERROR[$val];
    return false;
  }

  private function _getIntFromBin($offset, $size)
  {
    $val = ord($this->lnk_bin[$offset]);
    $currentoffset = $offset+1;
    $endoffset = $offset+$size;
    $bitshift = 8;
    for ($currentoffset;$currentoffset<$endoffset;$currentoffset++) {
      $val = $val | (ord($this->lnk_bin[$currentoffset]) << $bitshift);
      $bitshift = $bitshift + 8;
    }
    return $val;
  }


  private function _RealOffset($name,$include_me=false)
  {
    $size = 0;
    foreach ($this->StructSize as $key => $val) {
        if ($key == $name && $include_me == false) break;
        $size = $size + $val;
        if ($key == $name) break;
    }
    return $size;
  }


  public function open($filepath)
  {
    if (file_exists($filepath)) {
      $_stat_lnk = stat ($filepath);
      if (!$_stat_lnk) {
        return $this->_set_error(3);
      } else {
        $this->lnk_bin = fread(fopen($filepath, "rb"), filesize($filepath));
      }

      $parsed = $this->_is_msshlnk() &&  $this->_have_LinkCLSID() && $this->_get_LinkFlags();

      if ($parsed == true) {
        // get all offsets
        foreach ( $this->LinkFlags as $key => $val) {
          if ($this->H_FLAG[$key][1] == true) {
            $func="_OffsetFor$key";
            $this->$func();
          }
        }
      }
      return $parsed;
    } else {
      // file does not exist...
      return $this->_set_error(2);
    }
  }

  public function parse()
  {
    $this->get_CreationTime();
    $this->get_AccessTime();
    $this->get_WriteTime();
    foreach ( $this->LinkFlags as $key => $val) {
        if ($this->H_FLAG[$key][1] == true) {
            $func="parse_$key";
            $this->$func();
          }
    }
  }

  private function _is_msshlnk()
  {
    if ($this->lnk_bin[0] != 'L') {
      // really stop processing not msshlnk file...
      return $this->_set_error(1);
    }
    return true;
  } // _is_msshlnk

  private function _have_LinkCLSID()
  {
    if((ord($this->lnk_bin[4]) != 0x01) ||
      (ord($this->lnk_bin[5]) != 0x14) ||
      (ord($this->lnk_bin[6]) != 0x02) ||
      (ord($this->lnk_bin[7]) != 0x00) ||
      (ord($this->lnk_bin[8]) != 0x00) ||
      (ord($this->lnk_bin[9]) != 0x00) ||
      (ord($this->lnk_bin[10]) != 0x00) ||
      (ord($this->lnk_bin[11]) != 0x00) ||
      (ord($this->lnk_bin[12]) != 0xC0) ||
      (ord($this->lnk_bin[13]) != 0x00) ||
      (ord($this->lnk_bin[14]) != 0x00) ||
      (ord($this->lnk_bin[15]) != 0x00) ||
      (ord($this->lnk_bin[16]) != 0x00) ||
      (ord($this->lnk_bin[17]) != 0x00) ||
      (ord($this->lnk_bin[18]) != 0x00) ||
      (ord($this->lnk_bin[19]) != 0x46)) {
        return $this->_set_error(4);
    }
    return true;
  } // _have_LinkCLSID

  private function _get_LinkFlags()
  {
    // $flags = $this->_getIntFromBin(20, 4);
    $flags = $this->_getvalue(unpack('i',substr($this->lnk_bin,20,4)),1);
    foreach ($this->H_FLAG as $key => $val ) {
      if ($flags & $this->H_FLAG[$key][0]) {
        $this->LinkFlags[$key] = 1;
      }
    }
    $this->ParsedInfo['LinkFlags'] = $this->LinkFlags; // duplication but...
    return true;
  } // _get_LinkFlags

  private function _OffsetForHasLinkTargetIDList()
  {
    if (isset($this->LinkFlags['HasLinkTargetIDList'])) {
      // if it is set than we need to find how many bytes we need to
      // skip to get to LinkInfo structure...
       $this->StructSize['LinkTargetIDListSize'] = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('LinkTargetIDListSize'),2)),1) + 2 ;
       return true;
    }
    return true;
    // in reallity we always return true regardless of situation (needed for $this->open processing)
  }

  private function _OffsetForHasLinkInfo()
  {
    $this->StructSize['LinkInfoSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize'),4)),1) + 4;
  }

  private function _OffsetForHasName()
  {
    $this->StructSize['NameSize'] = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('NameSize')-2,2)),1);
  }

  private function _OffsetForHasRelativePath()
  {
    return true; // TODO
    $relativepath_size = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,2)),1);
    //echo "relativepath_size=" . $relativepath_size . PHP_EOL;
    //echo "this->_RealOffset('RelativePathSize',true) == " . $this->_RealOffset('RelativePathSize',true) . PHP_EOL;
    //echo "this->_RealOffset('RelativePathSize') == " . $this->_RealOffset('RelativePathSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['RelativePath'] = substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,$relativepath_size*2);

  }

  private function _OffsetForHasWorkingDir()
  {
    return true; // TODO
    $workingdir_size = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,2)),1);
    //echo "workingdir_size=" . $workingdir_size . PHP_EOL;
    //echo "this->_RealOffset('WorkingDirSize',true) == " . $this->_RealOffset('WorkingDirSize',true) . PHP_EOL;
    //echo "this->_RealOffset('WorkingDirSize') == " . $this->_RealOffset('WorkingDirSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['WorkingDir'] = substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,$workingdir_size*2);
  }

  private function _OffsetForHasArguments()
  {
    $this->StructSize['ArgumentsSize'] = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('ArgumentsSize'),2)),1);
  }

  //@todo to be done
  private function _OffsetForHasIconLocation() {}
  private function _OffsetForHasExpString() {}
  private function _OffsetForHasDarwinID() {}
  private function _OffsetForHasExpIcon() {}
  private function _OffsetForRunWithShimLayer() {}
  private function _OffsetForEnableTargetMetadata() {}


  public function get_FileAttributes()
  {
    $flags = $this->_getvalue(unpack('i',substr($this->lnk_bin,24,4)),1);
    foreach ($this->F_FLAG as $key => $val ) {
      if ($flags & $this->F_FLAG[$key]) {
        $this->FileAttributes[$key] = 1;
      }
    }
    return true;
  } // _get_File_Attributes

  public function get_CreationTime()
  {
    $this->ParsedInfo['CreationTime'] = unpack("H*",substr($this->lnk_bin,28,8));
  }

  public function get_AccessTime()
  {
    $this->ParsedInfo['AccessTime'] = unpack("H*",substr($this->lnk_bin,36,8));
  }

  public function get_WriteTime()
  {
    $this->ParsedInfo['WriteTime'] = unpack("H*",substr($this->lnk_bin,44,8));
  }

  public function get_FileSize()
  {
    $this->ParsedInfo['FileSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,52,4)),1);
  }

  public function get_IconIndex()
  {
    $this->ParsedInfo['IconIndex'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,56,4)),1);
  }


  public function get_ShowCommand()
  {
    $this->ParsedInfo['ShowCommand'] = $this->WM_CMD[$this->_getvalue(unpack('i',substr($this->lnk_bin,60,4)),1)];
  }

  public function get_HotKeyFlags()
  {
    // TODO
  }


  private function parse_guid($val)
  {
    $guid = '{';
    $guid = $guid . $this->_getvalue(unpack("H*",$val[3] . $val[2] . $val[1] . $val[0]),1);
    $guid = $guid . '-' . $this->_getvalue(unpack("H*", $val[5] . $val[4]),1);
    $guid = $guid . '-' . $this->_getvalue(unpack("H*", $val[7] . $val[6]),1);
    $guid = $guid . '-' . $this->_getvalue(unpack("H*", $val[8] . $val[9]),1);
    $guid = $guid . '-' . $this->_getvalue(unpack("H*", $val[10] . $val[11] . $val[12] . $val[13] . $val[14] . $val[15]),1);
    $guid = $guid . '}';
    return $guid;
  }

  private function parse_pidl_type($val)
  {
      foreach($this->PIDL as $key => $name)
      {
          if($key == $val) return $name;
      }
      return "PT_UNKNOWN";
  }

  private function _get_ItemIDSize($offset)
  {
      if ($this->ItemIDSize == -1)
      {
          $this->ItemIDSize = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('LinkTargetIDListSize')+2,2)),1);
      } else
      {
          $this->ItemIDSize = $this->_getvalue(unpack('v',substr($this->lnk_bin,$offset,2),1));
      }
      return $this->ItemIDSize;
  }
  public function parse_HasLinkTargetIDList()
  {
    return true; // TODO
    if (!isset($this->LinkFlags['HasLinkTargetIDList'])) return $this->_set_error(5);
    var_dump("----------------------");
    while ($this->_get_ItemIDSize() != 0)
    {
        $val = substr($this->lnk_bin,$this->ItemIDSize,$ItemIDSize);
        if($ItemIDSize == 20)
        {
            var_dump($this->parse_guid($val));
        }
        else
        {
            var_dump($val);
        }
        $initial_offset = $initial_offset + $ItemIDSize -2;
        $ItemIDSize = $this->_getvalue(unpack('v',substr($this->lnk_bin,$initial_offset,2)),1);
    }
  } // parse_LinkTargetIDList




  // LINKINFO PARSING FUNCTIONS
  private function _get_NetworkProvider($nettype)
  {
    //
    foreach($this->NET_PROVIDER_TYPE as $key => $val) {
      if($key == $nettype) return $this->NET_PROVIDER_TYPE[$key];
    }
    return $this->_set_error(5);
  }

  public function parse_HasLinkInfo()
  {
    if (!isset($this->LinkFlags['HasLinkInfo'])) return $this->_set_error(5);

    $this->LinkInfo['LinkInfoSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize'),4)),1);
    $this->LinkInfo['LinkInfoHeaderSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 4,4)),1);
    $this->LinkInfo['VolumeIDOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 12,4)),1);
    if ($this->LinkInfo['LinkInfoHeaderSize'] >= 36) {
      // unicode locations...
      $this->LinkInfo['LocalBasePathOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 28,4)),1);
      $this->LinkInfo['CommonPathSuffixOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 32,4)),1);
    } else {
      $this->LinkInfo['LocalBasePathOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 16,4)),1);
      $this->LinkInfo['CommonPathSuffixOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 24,4)),1);
    }
    $this->LinkInfo['CommonNetworkRelativeLinkOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 20,4)),1);
    $flags = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 8,4)),1);
    foreach ($this->L_IFLAGS as $key => $val ) {
      if ($flags & $this->L_IFLAGS[$key]) $this->LinkInfoFlags[] = $key;
    }
    $this->ParsedInfo['LinkInfo']['LinkInfoFlags'] = $this->LinkInfoFlags;
    $localbasepath_size = $this->LinkInfo['LinkInfoSize'] - $this->LinkInfo['CommonPathSuffixOffset'];
    $this->ParsedInfo['LinkInfo']['CommonPathSuffix'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonPathSuffixOffset']),$localbasepath_size))),0);

    // no need for foreach in here but just be consistent and future proof (if they decide to add more flags in future....)
    foreach ($this->LinkInfoFlags as $val) {
      $func = "_IfSet_$val";
      $this->$func();
    }
    //print_r($this->LinkInfo);
    return true;
  } // parse_LinkInfo



  private function _IfSet_VolumeIDAndLocalBasePath ()
  {
    // just chilling here...
    $this->LinkInfo['VolumeIDSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset']),4)),1);
    $this->ParsedInfo['LinkInfo']['VolumeID']['DriveType'] = $this->DRIVE_TYPE[$this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 4),4)),1)];
    $this->ParsedInfo['LinkInfo']['VolumeID']['DriveSerialNumber'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 8),4)),1);

    $VolumeLabelOffset = $this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 12),4)),1);
    if ($VolumeLabelOffset == 20) { // must ignore this offset and use unicode based one
      $VolumeLabelOffsetUnicode = $this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),4)),1);
      $volume_id_size = $this->LinkInfo['VolumeIDSize'] - ($VolumeLabelOffsetUnicode + 4);
      // DO NOT just interpret this as is watch for local encoding on machine... - or better do not use local letters on storage names(labels)...
      $this->ParsedInfo['LinkInfo']['VolumeID']['VolumeLabel'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),$volume_id_size))),0);
    } else {
      $volume_id_size = $this->LinkInfo['VolumeIDSize'] - $VolumeLabelOffset;
      // WARNING WARNING this value is always in local codepage (what ever is on user machine DO NOT assume you know what it is....)
      $this->ParsedInfo['LinkInfo']['VolumeID']['VolumeLabel'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),$volume_id_size))),0);
    }

    $localbasepath_size = $this->LinkInfo['CommonPathSuffixOffset'] - $this->LinkInfo['LocalBasePathOffset'];

    if ($this->LinkInfo['LinkInfoHeaderSize'] >= 36) {
      $this->ParsedInfo['LinkInfo']['VolumeID']['LocalBasePath'] = $this->_getvalue(explode(chr(0), trim(mb_convert_encoding(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['LocalBasePathOffset']),$localbasepath_size), 'UTF-8','UTF-16LE'))),0);
    } else {
       $this->ParsedInfo['LinkInfo']['VolumeID']['LocalBasePath'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['LocalBasePathOffset']),$localbasepath_size))),0);
    }
  }

  private function _IfSet_CommonNetworkRelativeLinkAndPathSuffix ()
  {
    $this->LinkInfo['CommonNetworkRelativeLinkSize'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset']),4)),1);
    $this->LinkInfo['NetNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 8,4)),1);
    $this->LinkInfo['DeviceNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 12,4)),1);
    $flags = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 4,4)),1);
    foreach ($this->L_INFLAGS as $key => $val ) {
      if ($flags & $this->L_INFLAGS[$key]) $this->LinkInfoNetworkFlags[] = $key;
    }
    if( $this->LinkInfo['NetNameOffset'] >= 20) {
      $this->LinkInfo['NetNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 8,4)),1);
      $this->LinkInfo['DeviceNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 12,4)),1);
    } else {
      $this->LinkInfo['NetNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 20,4)),1);
      $this->LinkInfo['DeviceNameOffset'] = $this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 24,4)),1);
    }
      // no need for foreach in here but just be consistent and future proof (if they decide to add more flags in future....)
    foreach ($this->LinkInfoNetworkFlags as $val) {
      $func = "_IfSet_$val";
      $this->$func();
    }
  }

  private function _IfSet_ValidDevice()
  {
    $device_name_size = $this->LinkInfo['CommonNetworkRelativeLinkSize'] - $this->LinkInfo['DeviceNameOffset'];
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['DeviceName'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + $this->LinkInfo['DeviceNameOffset']),$device_name_size))),0);
  }

  private function _IfSet_ValidNetType()
  {
    if($this->LinkInfo['DeviceNameOffset'] != 0) {
      $net_name_size = $this->LinkInfo['DeviceNameOffset'] - $this->LinkInfo['NetNameOffset'];
    } else {
      $net_name_size = $this->LinkInfo['CommonNetworkRelativeLinkSize'] - $this->LinkInfo['NetNameOffset'];
    }
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['NetName'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + $this->LinkInfo['NetNameOffset']),$net_name_size))),0);
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['NetworkProviderType'] = $this->_get_NetworkProvider($this->_getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 16,4)),1));
  }
// END OF LINKINFO PARSING FUNCTIONS


  public function parse_HasName() {
    return true; // TODO
    if (!isset($this->LinkFlags['HasName'])) return $this->_set_error(5);
    $name_size = $this->_RealOffset('NameSize',true) - $this->_RealOffset('NameSize');
    echo "NAME_SIZE=" . $name_size . PHP_EOL;
    echo "this->_RealOffset('NameSize',true) == " . $this->_RealOffset('NameSize',true) . PHP_EOL;
    echo "this->_RealOffset('NameSize') == " . $this->_RealOffset('NameSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['Name'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,$this->_RealOffset('NameSize')-2,$name_size*2))),0);
  }
  public function parse_HasRelativePath() {
    return true; // TODO
    if (!isset($this->LinkFlags['HasRelativePath'])) return $this->_set_error(5);
    $relativepath_size = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,2)),1);
    $this->ParsedInfo['LinkInfo']['RelativePath'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,$relativepath_size*2))),0);
  }
  public function parse_HasWorkingDir() {
    return true; // TODO
    if (!isset($this->LinkFlags['HasWorkingDir'])) return $this->_set_error(5);
    $workingdir_size = $this->_getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,2)),1);
    $this->ParsedInfo['LinkInfo']['WorkingDir'] = $this->_getvalue(explode(chr(0), trim(substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,$workingdir_size*2))),0);
  }

  public function parse_HasArguments() {}

  public function parse_HasIconLocation() {}

  public function parse_HasExpString() {}

  public function parse_HasDarwinID() {}

  public function parse_HasExpIcon() {}

  public function parse_RunWithShimLayer() {}

  public function parse_EnableTargetMetadata() {}


}
// No unneed closing tag

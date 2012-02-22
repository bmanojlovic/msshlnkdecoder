<?php
class MSshlnk {
  public $lnk_bin = null;
  public $errno =0;
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

  function MSshlnk() {
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
  }

  function _pack_array($format,$arr) {
    return call_user_func_array('pack',array_merge(array($format),(array)$arr));
  }
  
  private function getvalue($array, $key) {
    return $array[$key];
  }


  private function _set_error($val) {
    $this->errno = $val; 
    $this->errstring = $this->_ERROR[$val];
    return false;
  }

  private function _getIntFromBin($offset, $size) {
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


  private function _RealOffset($name,$include_me=false) {
    $size = 0;
    foreach ($this->StructSize as $key => $val) {
        //echo $key . PHP_EOL;
        if ($key == $name && $include_me == false) break;
        $size = $size + $val;
        if ($key == $name) break;
    }
    //echo  PHP_EOL;
    return $size;
  }


  public function open($filepath) {
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

  public function parse() {
    foreach ( $this->LinkFlags as $key => $val) {
      if ($this->H_FLAG[$key][1] == true) {
        $func="parse_$key";
        $this->$func();
      }
    }
    $this->get_CreationTime();
    $this->get_AccessTime();
    $this->get_WriteTime();
    
  }
  private function _is_msshlnk() {
    if ($this->lnk_bin[0] != 'L') {
      // really stop processing not msshlnk file...
      return $this->_set_error(1);
    }
    return true;
  } // _is_msshlnk

  private function _have_LinkCLSID() {
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

  private function _get_LinkFlags() {
    // $flags = $this->_getIntFromBin(20, 4);
    $flags = $this->getvalue(unpack('i',substr($this->lnk_bin,20,4)),1);
    foreach ($this->H_FLAG as $key => $val ) {
      if ($flags & $this->H_FLAG[$key][0]) {
        $this->LinkFlags[$key] = 1;
      }
    }
    $this->ParsedInfo['LinkFlags'] = $this->LinkFlags; // duplication but...
    return true;
  } // _get_LinkFlags

  private function _OffsetForHasLinkTargetIDList() {
    if (isset($this->LinkFlags['HasLinkTargetIDList'])) {
      // if it is set than we need to find how many bytes we need to 
      // skip to get to LinkInfo structure...
       $this->StructSize['LinkTargetIDListSize'] = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('LinkTargetIDListSize'),2)),1) + 2 ;
       return true;
    }
    return true;
    // in reallity we always return true regardless of situation (needed for $this->open processing)
  }

  private function _OffsetForHasLinkInfo() {
    $this->StructSize['LinkInfoSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize'),4)),1) + 4;
  }

  private function _OffsetForHasName() {
    $this->StructSize['NameSize'] = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('NameSize')-4,2)),1);
  }

  private function _OffsetForHasRelativePath() {
    $this->StructSize['RelativePathSize'] = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('RelativePathSize'),2)),1);
  }

  private function _OffsetForHasWorkingDir() {
    $this->StructSize['WorkingDirSize'] = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize'),2)),1);
  }

  private function _OffsetForHasArguments() {
    $this->StructSize['ArgumentsSize'] = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('ArgumentsSize'),2)),1);
  }

  private function _OffsetForHasIconLocation() {}
  private function _OffsetForHasExpString() {}
  private function _OffsetForHasDarwinID() {}
  private function _OffsetForHasExpIcon() {}
  private function _OffsetForRunWithShimLayer() {}
  private function _OffsetForEnableTargetMetadata() {}


  public function get_FileAttributes() {
    $flags = $this->getvalue(unpack('i',substr($this->lnk_bin,24,4)),1);
    foreach ($this->F_FLAG as $key => $val ) {
      if ($flags & $this->F_FLAG[$key]) {
        $this->FileAttributes[$key] = 1;
      }
    }
    return true;
  } // _get_File_Attributes

  public function get_CreationTime() {
    $this->ParsedInfo['CreationTime'] = unpack("H*",substr($this->lnk_bin,28,8));
  }
  public function get_AccessTime() {
   $this->ParsedInfo['AccessTime'] = unpack("H*",substr($this->lnk_bin,36,8));
  }
  public function get_WriteTime() {
    $this->ParsedInfo['WriteTime'] = unpack("H*",substr($this->lnk_bin,44,8));
  }


  public function get_FileSize() {
    $this->ParsedInfo['FileSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,52,4)),1);
  }

  public function get_IconIndex() {
    $this->ParsedInfo['IconIndex'] = $this->getvalue(unpack('i',substr($this->lnk_bin,56,4)),1);
  }


  public function get_ShowCommand() {
    $this->ParsedInfo['ShowCommand'] = $this->WM_CMD[$this->getvalue(unpack('i',substr($this->lnk_bin,60,4)),1)];
  }

  public function get_HotKeyFlags() {
    // TODO
  }




  public function parse_HasLinkTargetIDList() {
    return true;
    if (!isset($this->LinkFlags['HasLinkTargetIDList'])) return $this->_set_error(5);
      print ( "XX=" . substr($this->lnk_bin,$this->_RealOffset('LinkTargetIDListSize'),$this->_RealOffset('LinkTargetIDListSize',true) - ($this->_RealOffset('LinkTargetIDListSize')+2)) . PHP_EOL);
      $ItemIDSize = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('LinkTargetIDListSize')+2,2)),1);
      $initial_offset = $this->_RealOffset('LinkTargetIDListSize') + 2;
      print ("_RealOffset('LinkTargetIDListSize')=" . $this->_RealOffset('LinkTargetIDListSize') . PHP_EOL);
      print ("ItemIDSize=" . $ItemIDSize . PHP_EOL);
      print ("initial_offset=" . $initial_offset . PHP_EOL);
      $ItemIDSize = $this->getvalue(unpack('v',substr($this->lnk_bin,$initial_offset + $ItemIDSize ,2)),1);
      $initial_offset = $initial_offset + $ItemIDSize;
      print ("ItemIDSize=" . $ItemIDSize . PHP_EOL);
      print ("initial_offset=" . $initial_offset . PHP_EOL);
      $ItemIDSize = $this->getvalue(unpack('v',substr($this->lnk_bin,$initial_offset + $ItemIDSize ,2)),1);
      $initial_offset = $initial_offset + $ItemIDSize;
      print ("ItemIDSize=" . $ItemIDSize . PHP_EOL);
      print ("initial_offset=" . $initial_offset . PHP_EOL);
      $ItemIDSize = $this->getvalue(unpack('v',substr($this->lnk_bin,$initial_offset + $ItemIDSize ,2)),1);
      $initial_offset = $initial_offset + $ItemIDSize;
      print ("ItemIDSize=" . $ItemIDSize . PHP_EOL);
      print ("initial_offset=" . $initial_offset . PHP_EOL);
      
      //var_dump($this->_RealOffset('LinkTargetIDListSize',true));


  } // parse_LinkTargetIDList




  // LINKINFO PARSING FUNCTIONS
  private function _get_NetworkProvider($nettype) {
    //
    foreach($this->NET_PROVIDER_TYPE as $key => $val) {
      if($key == $nettype) return $this->NET_PROVIDER_TYPE[$key];
    }
    return $this->_set_error(5);
  }

  public function parse_HasLinkInfo() {
    if (!isset($this->LinkFlags['HasLinkInfo'])) return $this->_set_error(5);

    $this->LinkInfo['LinkInfoSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize'),4)),1);
    $this->LinkInfo['LinkInfoHeaderSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 4,4)),1);
    $this->LinkInfo['VolumeIDOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 12,4)),1);
    if ($this->LinkInfo['LinkInfoHeaderSize'] >= 36) {
      // unicode locations...
      $this->LinkInfo['LocalBasePathOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 28,4)),1);
      $this->LinkInfo['CommonPathSuffixOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 32,4)),1);
    } else {
      $this->LinkInfo['LocalBasePathOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 16,4)),1);
      $this->LinkInfo['CommonPathSuffixOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 24,4)),1);
    }
    $this->LinkInfo['CommonNetworkRelativeLinkOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 20,4)),1);
    $flags = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + 8,4)),1);
    foreach ($this->L_IFLAGS as $key => $val ) {
      if ($flags & $this->L_IFLAGS[$key]) $this->LinkInfoFlags[] = $key;
    }
    $this->ParsedInfo['LinkInfo']['LinkInfoFlags'] = $this->LinkInfoFlags;
    $localbasepath_size = $this->LinkInfo['LinkInfoSize'] - $this->LinkInfo['CommonPathSuffixOffset'];
    $this->ParsedInfo['LinkInfo']['CommonPathSuffix'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonPathSuffixOffset']),$localbasepath_size);

    // no need for foreach in here but just be consistent and future proof (if they decide to add more flags in future....)
    foreach ($this->LinkInfoFlags as $val) {
      $func = "_IfSet_$val";
      $this->$func();
    }
    //print_r($this->LinkInfo);
    return true;
  } // parse_LinkInfo



  private function _IfSet_VolumeIDAndLocalBasePath () {
    // just chilling here...
    $this->LinkInfo['VolumeIDSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset']),4)),1);
    $this->ParsedInfo['LinkInfo']['VolumeID']['DriveType'] = $this->DRIVE_TYPE[$this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 4),4)),1)];
    $this->ParsedInfo['LinkInfo']['VolumeID']['DriveSerialNumber'] = $this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 8),4)),1);

    $VolumeLabelOffset = $this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 12),4)),1);
    if ($VolumeLabelOffset == 20) { // must ignore this offset and use unicode based one
      $VolumeLabelOffsetUnicode = $this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),4)),1);
      $volume_id_size = $this->LinkInfo['VolumeIDSize'] - ($VolumeLabelOffsetUnicode + 4);
      // DO NOT just interpret this as is watch for local encoding on machine... - or better do not use local letters on storage names(labels)...
      $this->ParsedInfo['LinkInfo']['VolumeID']['VolumeLabel'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),$volume_id_size);
    } else {
      $volume_id_size = $this->LinkInfo['VolumeIDSize'] - $VolumeLabelOffset;
      // WARNING WARNING this value is always in local codepage (what ever is on user machine DO NOT assume you know what it is....)
      $this->ParsedInfo['LinkInfo']['VolumeID']['VolumeLabel'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['VolumeIDOffset'] + 16),$volume_id_size);
    }

    $localbasepath_size = $this->LinkInfo['CommonPathSuffixOffset'] - $this->LinkInfo['LocalBasePathOffset'];

    if ($this->LinkInfo['LinkInfoHeaderSize'] >= 36) {
      $this->ParsedInfo['LinkInfo']['VolumeID']['LocalBasePath'] = mb_convert_encoding(substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['LocalBasePathOffset']),$localbasepath_size), 'UTF-8','UTF-16LE');
    } else {
       $this->ParsedInfo['LinkInfo']['VolumeID']['LocalBasePath'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['LocalBasePathOffset']),$localbasepath_size);
    }
  }

  private function _IfSet_CommonNetworkRelativeLinkAndPathSuffix () {
    $this->LinkInfo['CommonNetworkRelativeLinkSize'] = $this->getvalue(unpack('i',substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset']),4)),1);
    $this->LinkInfo['NetNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 8,4)),1);
    $this->LinkInfo['DeviceNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 12,4)),1);
    $flags = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 4,4)),1);
    foreach ($this->L_INFLAGS as $key => $val ) {
      if ($flags & $this->L_INFLAGS[$key]) $this->LinkInfoNetworkFlags[] = $key;
    }
    if( $this->LinkInfo['NetNameOffset'] >= 20) {
      $this->LinkInfo['NetNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 8,4)),1);
      $this->LinkInfo['DeviceNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 12,4)),1);
    } else {
      $this->LinkInfo['NetNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 20,4)),1);
      $this->LinkInfo['DeviceNameOffset'] = $this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 24,4)),1);
    }
      // no need for foreach in here but just be consistent and future proof (if they decide to add more flags in future....)
    foreach ($this->LinkInfoNetworkFlags as $val) {
      $func = "_IfSet_$val";
      $this->$func();
    }
  }

  private function _IfSet_ValidDevice() {
    $device_name_size = $this->LinkInfo['CommonNetworkRelativeLinkSize'] - $this->LinkInfo['DeviceNameOffset'];
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['DeviceName'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + $this->LinkInfo['DeviceNameOffset']),$device_name_size);
  }

  private function _IfSet_ValidNetType(){
    if($this->LinkInfo['DeviceNameOffset'] != 0) {
      $net_name_size = $this->LinkInfo['DeviceNameOffset'] - $this->LinkInfo['NetNameOffset'];
    } else {
      $net_name_size = $this->LinkInfo['CommonNetworkRelativeLinkSize'] - $this->LinkInfo['NetNameOffset'];
    }
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['NetName'] = substr($this->lnk_bin,($this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + $this->LinkInfo['NetNameOffset']),$net_name_size);
    $this->ParsedInfo['LinkInfo']['CommonNetworkRelative']['NetworkProviderType'] = $this->_get_NetworkProvider($this->getvalue(unpack('i',substr($this->lnk_bin,$this->_RealOffset('LinkInfoSize') + $this->LinkInfo['CommonNetworkRelativeLinkOffset'] + 16,4)),1));
  }
// END OF LINKINFO PARSING FUNCTIONS


  public function parse_HasName() {
    if (!isset($this->LinkFlags['HasName'])) return $this->_set_error(5);
    $name_size = $this->_RealOffset('NameSize',true) - $this->_RealOffset('NameSize');
    echo "NAME_SIZE=" . $name_size . PHP_EOL;
    echo "this->_RealOffset('NameSize',true) == " . $this->_RealOffset('NameSize',true) . PHP_EOL;
    echo "this->_RealOffset('NameSize') == " . $this->_RealOffset('NameSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['Name'] = substr($this->lnk_bin,$this->_RealOffset('NameSize')-2,$name_size*2);
  }
  public function parse_HasRelativePath() {
    if (!isset($this->LinkFlags['HasRelativePath'])) return $this->_set_error(5);
    $relativepath_size = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,2)),1);
    echo "relativepath_size=" . $relativepath_size . PHP_EOL;
    echo "this->_RealOffset('RelativePathSize',true) == " . $this->_RealOffset('RelativePathSize',true) . PHP_EOL;
    echo "this->_RealOffset('RelativePathSize') == " . $this->_RealOffset('RelativePathSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['RelativePath'] = substr($this->lnk_bin,$this->_RealOffset('RelativePathSize')-2,$relativepath_size*2);
  }
  public function parse_HasWorkingDir() {
    if (!isset($this->LinkFlags['HasWorkingDir'])) return $this->_set_error(5);
    $workingdir_size = $this->getvalue(unpack('v',substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,2)),1);
    echo "workingdir_size=" . $workingdir_size . PHP_EOL;
    echo "this->_RealOffset('WorkingDirSize',true) == " . $this->_RealOffset('WorkingDirSize',true) . PHP_EOL;
    echo "this->_RealOffset('WorkingDirSize') == " . $this->_RealOffset('WorkingDirSize') . PHP_EOL;
    $this->ParsedInfo['LinkInfo']['WorkingDir'] = substr($this->lnk_bin,$this->_RealOffset('WorkingDirSize')-2,$workingdir_size*2);
  }
  public function parse_HasArguments() {}
  public function parse_HasIconLocation() {}
  public function parse_HasExpString() {}
  public function parse_HasDarwinID() {}
  public function parse_HasExpIcon() {}
  public function parse_RunWithShimLayer() {}
  public function parse_EnableTargetMetadata() {}


}
?>
